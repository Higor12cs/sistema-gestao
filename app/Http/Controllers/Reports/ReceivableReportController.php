<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\Receivable;
use App\Services\Reports\BaseReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReceivableReportController extends Controller
{
    protected BaseReportService $reportService;

    public function __construct(BaseReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function analytical(Request $request)
    {
        $query = Receivable::query()
            ->with([
                'customer',
                'chartAccount',
                'paymentMethod',
                'order',
                'createdBy',
            ]);

        $this->applyFilters($query, $request);

        $receivables = $query->orderBy($this->getOrderByField($request), 'desc')->get();

        $filename = 'RelatorioRecebiveisAnalitico';
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $filename .= '_'.$request->get('start_date').'_'.$request->get('end_date');
        }
        $filename .= '.pdf';

        return $this->reportService->streamPdf('reports.receivables.analytical', [
            'receivables' => $receivables,
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'date_type' => $request->get('date_type', 'issue_date'),
            'status' => $request->get('status', 'all'),
        ], $filename);
    }

    public function synthetic(Request $request)
    {
        $query = Receivable::query()
            ->with([
                'customer',
                'chartAccount',
                'paymentMethod',
                'order',
                'createdBy',
            ]);

        $this->applyFilters($query, $request);

        $receivables = $query->get();

        $totalReceivables = $receivables->count();
        $totalAmount = $receivables->sum('total_amount');
        $totalPaid = $receivables->sum('paid_amount');
        $totalRemaining = $totalAmount - $totalPaid;

        $groupBy = $request->get('group_by', 'day');
        $dateField = $this->getDateField($request);
        $format = $groupBy === 'day' ? 'Y-m-d' : ($groupBy === 'week' ? 'Y-W' : 'Y-m');

        $groupedReceivables = $receivables->groupBy(function ($receivable) use ($dateField, $format) {
            return $receivable->$dateField->format($format);
        });

        $summaryData = [];
        foreach ($groupedReceivables as $key => $group) {
            $summaryData[] = [
                'period' => $key,
                'label' => $this->formatPeriodLabel($key, $groupBy),
                'count' => $group->count(),
                'total' => $group->sum('total_amount'),
                'paid' => $group->sum('paid_amount'),
                'remaining' => $group->sum('total_amount') - $group->sum('paid_amount'),
                'avg_amount' => $group->count() > 0 ? $group->sum('total_amount') / $group->count() : 0,
            ];
        }

        usort($summaryData, fn ($a, $b) => strcmp($a['period'], $b['period']));

        $topCustomers = $receivables->groupBy('customer_id')
            ->map(function ($g) {
                $customer = $g->first()->customer;

                return [
                    'customer' => $customer ? $customer->first_name.' '.$customer->last_name : 'N/A',
                    'count' => $g->count(),
                    'total' => $g->sum('total_amount'),
                    'paid' => $g->sum('paid_amount'),
                    'remaining' => $g->sum('total_amount') - $g->sum('paid_amount'),
                ];
            })->sortByDesc('total')->take(10)->values()->toArray();

        $topChartAccounts = $receivables->groupBy('chart_account_id')
            ->map(function ($g) {
                $chartAccount = $g->first()->chartAccount;

                return [
                    'chart_account' => $chartAccount ? $chartAccount->name : 'N/A',
                    'code' => $chartAccount ? $chartAccount->code : 'N/A',
                    'count' => $g->count(),
                    'total' => $g->sum('total_amount'),
                    'paid' => $g->sum('paid_amount'),
                    'remaining' => $g->sum('total_amount') - $g->sum('paid_amount'),
                ];
            })->sortByDesc('total')->take(10)->values()->toArray();

        $filename = 'RelatorioRecebiveisSintetico';
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $filename .= '_'.$request->get('start_date').'_'.$request->get('end_date');
        }
        $filename .= '.pdf';

        return $this->reportService->streamPdf('reports.receivables.synthetic', [
            'receivables' => $receivables,
            'totalReceivables' => $totalReceivables,
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalRemaining' => $totalRemaining,
            'avgAmount' => $totalReceivables > 0 ? $totalAmount / $totalReceivables : 0,
            'summaryData' => $summaryData,
            'topCustomers' => $topCustomers,
            'topChartAccounts' => $topChartAccounts,
            'groupBy' => $groupBy,
            'date_type' => $request->get('date_type', 'issue_date'),
            'status' => $request->get('status', 'all'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ], $filename);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->get('customer_id'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $dateField = $this->getDateField($request);
            $query->whereBetween($dateField, [$request->get('start_date'), $request->get('end_date')]);
        }

        if ($request->filled('status') && $request->get('status') !== 'all') {
            if ($request->get('status') === 'paid') {
                $query->whereColumn('paid_amount', '=', 'total_amount');
            } elseif ($request->get('status') === 'open') {
                $query->whereColumn('paid_amount', '<', 'total_amount');
            }
        }

        if ($request->filled('chart_account_id')) {
            $chartAccountId = $request->get('chart_account_id');
            $chartAccount = ChartAccount::find($chartAccountId);

            if ($chartAccount) {
                $accountIds = $this->getAllChildChartAccountIds($chartAccount->first());
                $accountIds[] = $chartAccountId;

                $query->whereIn('chart_account_id', $accountIds);
            }
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->get('created_by'));
        }
    }

    private function getDateField(Request $request)
    {
        $dateType = $request->get('date_type', 'issue_date');

        switch ($dateType) {
            case 'due_date':
                return 'due_date';
            case 'payment_date':
                return 'issue_date';
            case 'issue_date':
            default:
                return 'issue_date';
        }
    }

    private function getOrderByField(Request $request)
    {
        return $this->getDateField($request);
    }

    private function getAllChildChartAccountIds(ChartAccount $chartAccount)
    {
        $childrenIds = [];

        foreach ($chartAccount->children as $child) {
            $childrenIds[] = $child->id;
            $childrenIds = array_merge($childrenIds, $this->getAllChildChartAccountIds($child));
        }

        return $childrenIds;
    }

    private function formatPeriodLabel($period, $groupBy)
    {
        if ($groupBy === 'day') {
            return Carbon::createFromFormat('Y-m-d', $period)->format('d/m/Y');
        }
        if ($groupBy === 'week') {
            [$year, $week] = explode('-', $period);
            $startOfWeek = Carbon::create($year)->setISODate($year, $week)->startOfWeek();
            $endOfWeek = Carbon::create($year)->setISODate($year, $week)->endOfWeek();

            return $startOfWeek->format('d/m/Y').' - '.$endOfWeek->format('d/m/Y');
        }

        return Carbon::createFromFormat('Y-m', $period)->format('M/Y');
    }
}
