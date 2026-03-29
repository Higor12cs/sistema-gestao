<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Reports\BaseReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    protected BaseReportService $reportService;

    public function __construct(BaseReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function analytical(Request $request)
    {
        $orders = Order::query()
            ->with([
                'customer',
                'seller',
                'createdBy',
                'items',
                'items.product',
            ])
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->get('customer_id')))
            ->when($request->filled('seller_id'), fn ($q) => $q->where('seller_id', $request->get('seller_id')))
            ->when($request->filled('created_by'), fn ($q) => $q->where('created_by', $request->get('created_by')))
            ->when(
                $request->filled('start_date') && $request->filled('end_date'),
                fn ($q) => $q->whereBetween('issue_date', [$request->get('start_date'), $request->get('end_date')])
            )
            ->orderBy('issue_date', 'desc')
            ->get();

        $filename = 'RelatorioPedidosAnalitico';
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $filename .= '_'.$request->get('start_date').'_'.$request->get('end_date');
        }
        $filename .= '.pdf';

        return $this->reportService->streamPdf('reports.orders.analytical', [
            'orders' => $orders,
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ], $filename);
    }

    public function synthetic(Request $request)
    {
        $query = $this->buildOrderQuery($request);

        $ordersWithItems = $query->with('items.product')->get();

        $totalOrders = $ordersWithItems->count();
        $totalSales = $ordersWithItems->sum('total_price');
        $totalItems = $ordersWithItems->flatMap(fn ($order) => $order->items)->sum('quantity');
        $avgTicket = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $summaryData = $this->getSummaryByPeriod($ordersWithItems, $request->get('group_by', 'day'));

        $topCustomers = $this->getTopCustomers($ordersWithItems);
        $topSellers = $this->getTopSellers($ordersWithItems);

        $filename = 'RelatorioPedidosSintetico';
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $filename .= '_'.$request->get('start_date').'_'.$request->get('end_date');
        }
        $filename .= '.pdf';

        return $this->reportService->streamPdf('reports.orders.synthetic', [
            'orders' => $ordersWithItems,
            'totalOrders' => $totalOrders,
            'totalSales' => $totalSales,
            'totalItems' => $totalItems,
            'avgTicket' => $avgTicket,
            'summaryData' => $summaryData,
            'topCustomers' => $topCustomers,
            'topSellers' => $topSellers,
            'groupBy' => $request->get('group_by', 'day'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ], $filename);
    }

    private function buildOrderQuery(Request $request)
    {
        return Order::query()
            ->with(['customer', 'seller', 'createdBy'])
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->get('customer_id')))
            ->when($request->filled('seller_id'), fn ($q) => $q->where('seller_id', $request->get('seller_id')))
            ->when($request->filled('created_by'), fn ($q) => $q->where('created_by', $request->get('created_by')))
            ->when(
                $request->filled('start_date') && $request->filled('end_date'),
                fn ($q) => $q->whereBetween('issue_date', [$request->get('start_date'), $request->get('end_date')])
            );
    }

    private function getSummaryByPeriod($orders, $groupBy = 'day')
    {
        $format = $groupBy === 'day' ? 'Y-m-d' : ($groupBy === 'week' ? 'Y-W' : 'Y-m');
        $groupedOrders = $orders->groupBy(fn ($order) => $order->issue_date->format($format));

        $summaryData = [];
        foreach ($groupedOrders as $key => $group) {
            $summaryData[] = [
                'period' => $key,
                'label' => $this->formatPeriodLabel($key, $groupBy),
                'count' => $group->count(),
                'total' => $group->sum('total_price'),
                'average' => $group->count() > 0 ? $group->sum('total_price') / $group->count() : 0,
                'items_count' => $group->flatMap(fn ($order) => $order->items)->sum('quantity'),
            ];
        }

        usort($summaryData, fn ($a, $b) => strcmp($a['period'], $b['period']));

        return $summaryData;
    }

    private function getTopCustomers($orders)
    {
        return $orders->groupBy('customer_id')
            ->map(function ($g) {
                $customer = $g->first()->customer;

                return [
                    'customer' => $customer ? $customer->first_name.' '.$customer->last_name : 'N/A',
                    'count' => $g->count(),
                    'total' => $g->sum('total_price'),
                ];
            })->sortByDesc('total')->take(10)->values()->toArray();
    }

    private function getTopSellers($orders)
    {
        return $orders->groupBy('seller_id')
            ->map(function ($g) {
                return [
                    'seller' => $g->first()->seller ? $g->first()->seller->name : 'N/A',
                    'count' => $g->count(),
                    'total' => $g->sum('total_price'),
                ];
            })->sortByDesc('total')->take(10)->values()->toArray();
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
