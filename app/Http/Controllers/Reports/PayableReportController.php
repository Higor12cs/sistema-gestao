<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\Payable;
use App\Services\Reports\BaseReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayableReportController extends Controller
{
    protected BaseReportService $reportService;

    public function __construct(BaseReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function analytical(Request $request)
    {
        $query = Payable::query()
            ->with([
                'supplier',
                'chartAccount',
                'paymentMethod',
                'purchase',
                'createdBy',
            ]);

        $this->applyFilters($query, $request);

        $payables = $query->orderBy($this->getOrderByField($request), 'desc')->get();

        $filename = 'RelatorioPageisAnalitico';
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $filename .= '_'.$request->get('start_date').'_'.$request->get('end_date');
        }
        $filename .= '.pdf';

        return $this->reportService->streamPdf('reports.payables.analytical', [
            'payables' => $payables,
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'date_type' => $request->get('date_type', 'issue_date'),
            'status' => $request->get('status', 'all'),
        ], $filename);
    }

    public function synthetic(Request $request)
    {
        $query = Payable::query()
            ->with([
                'supplier',
                'chartAccount',
                'paymentMethod',
                'purchase',
                'createdBy',
            ]);

        $this->applyFilters($query, $request);

        $payables = $query->get();

        $totalPayables = $payables->count();
        $totalAmount = $payables->sum('total_amount');
        $totalPaid = $payables->sum('paid_amount');
        $totalRemaining = $totalAmount - $totalPaid;

        $groupBy = $request->get('group_by', 'day');
        $dateField = $this->getDateField($request);
        $format = $groupBy === 'day' ? 'Y-m-d' : ($groupBy === 'week' ? 'Y-W' : 'Y-m');

        $groupedPayables = $payables->groupBy(function ($payable) use ($dateField, $format) {
            return $payable->$dateField->format($format);
        });

        $summaryData = [];
        foreach ($groupedPayables as $key => $group) {
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

        $topSuppliers = $payables->groupBy('supplier_id')
            ->map(function ($g) {
                $supplier = $g->first()->supplier;

                return [
                    'supplier' => $supplier ? $supplier->first_name.' '.$supplier->last_name : 'N/A',
                    'count' => $g->count(),
                    'total' => $g->sum('total_amount'),
                    'paid' => $g->sum('paid_amount'),
                    'remaining' => $g->sum('total_amount') - $g->sum('paid_amount'),
                ];
            })->sortByDesc('total')->take(10)->values()->toArray();

        $topChartAccounts = $payables->groupBy('chart_account_id')
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

        $filename = 'RelatorioPageisSintetico';
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $filename .= '_'.$request->get('start_date').'_'.$request->get('end_date');
        }
        $filename .= '.pdf';

        return $this->reportService->streamPdf('reports.payables.synthetic', [
            'payables' => $payables,
            'totalPayables' => $totalPayables,
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalRemaining' => $totalRemaining,
            'avgAmount' => $totalPayables > 0 ? $totalAmount / $totalPayables : 0,
            'summaryData' => $summaryData,
            'topSuppliers' => $topSuppliers,
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
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
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
