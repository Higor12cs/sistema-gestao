<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class OrderReportController extends Controller
{
    public function analytical(Request $request)
    {
        $orders = Order::query()
            ->with(["customer", "seller", "createdBy", "items", "items.product"])
            ->when($request->filled("customer_id"), fn($q) => $q->where("customer_id", $request->get("customer_id")))
            ->when($request->filled("seller_id"), fn($q) => $q->where("seller_id", $request->get("seller_id")))
            ->when($request->filled("created_by"), fn($q) => $q->where("created_by", $request->get("created_by")))
            ->when($request->filled("start_date") && $request->filled("end_date"), fn($q) =>
                $q->whereBetween("issue_date", [$request->get("start_date"), $request->get("end_date")]))
            ->get();

        $html = view('reports.orders.analytical', [
            'orders' => $orders,
            'start_date' => $request->get("start_date"),
            "end_date" => $request->get("end_date"),
        ])->render();

        $pdf = Browsershot::html($html)
            ->setNodeBinary('/usr/bin/node')
            ->setNpmBinary('/usr/bin/npm')
            ->noSandbox()
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->margins(10, 10, 10, 10)
            ->format('A4')
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="RelatorioPedidosAnalitico_' . $request->get("start_date") . '_' . $request->get("end_date") . '.pdf"');
    }

    public function synthetic(Request $request)
    {
        $query = Order::query()
            ->with(['customer', 'seller', 'createdBy'])
            ->when($request->filled('customer_id'), fn($q) => $q->where('customer_id', $request->get('customer_id')))
            ->when($request->filled('seller_id'), fn($q) => $q->where('seller_id', $request->get('seller_id')))
            ->when($request->filled('created_by'), fn($q) => $q->where('created_by', $request->get('created_by')))
            ->when($request->filled('start_date') && $request->filled('end_date'), fn($q) =>
                $q->whereBetween('issue_date', [$request->get('start_date'), $request->get('end_date')]));

        $ordersWithItems = $query->with('items.product')->get();
        $totalOrders = $ordersWithItems->count();
        $totalSales = $ordersWithItems->sum('total_price');
        $totalItems = $ordersWithItems->flatMap(fn($order) => $order->items)->sum('quantity');

        $groupBy = $request->get('group_by', 'day');
        $format = $groupBy === 'day' ? 'Y-m-d' : ($groupBy === 'week' ? 'Y-W' : 'Y-m');
        $groupedOrders = $ordersWithItems->groupBy(fn($order) => $order->issue_date->format($format));

        $summaryData = [];
        foreach ($groupedOrders as $key => $group) {
            $summaryData[] = [
                'period' => $key,
                'label' => $this->formatPeriodLabel($key, $groupBy),
                'count' => $group->count(),
                'total' => $group->sum('total_price'),
                'average' => $group->count() > 0 ? $group->sum('total_price') / $group->count() : 0,
                'items_count' => $group->flatMap(fn($order) => $order->items)->sum('quantity'),
            ];
        }

        usort($summaryData, fn($a, $b) => strcmp($a['period'], $b['period']));

        $topCustomers = $ordersWithItems->groupBy('customer_id')
            ->map(function ($g) {
                $customer = $g->first()->customer;
                return [
                    'customer' => $customer ? $customer->first_name . ' ' . $customer->last_name : 'N/A',
                    'count' => $g->count(),
                    'total' => $g->sum('total_price'),
                ];
            })->sortByDesc('total')->take(10)->values()->toArray();

        $topSellers = $ordersWithItems->groupBy('seller_id')
            ->map(function ($g) {
                return [
                    'seller' => $g->first()->seller ? $g->first()->seller->name : 'N/A',
                    'count' => $g->count(),
                    'total' => $g->sum('total_price'),
                ];
            })->sortByDesc('total')->take(10)->values()->toArray();

        $html = view('reports.orders.synthetic', [
            'orders' => $ordersWithItems,
            'totalOrders' => $totalOrders,
            'totalSales' => $totalSales,
            'totalItems' => $totalItems,
            'avgTicket' => $totalOrders > 0 ? $totalSales / $totalOrders : 0,
            'summaryData' => $summaryData,
            'topCustomers' => $topCustomers,
            'topSellers' => $topSellers,
            'groupBy' => $groupBy,
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ])->render();

        $pdf = Browsershot::html($html)
            ->setNodeBinary('/usr/bin/node')
            ->setNpmBinary('/usr/bin/npm')
            ->noSandbox()
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->margins(10, 10, 10, 10)
            ->format('A4')
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="RelatorioPedidosSintetico_' . $request->get("start_date") . '_' . $request->get("end_date") . '.pdf"');
    }

    private function formatPeriodLabel($period, $groupBy)
    {
        if ($groupBy === 'day')
            return \Carbon\Carbon::createFromFormat('Y-m-d', $period)->format('d/m/Y');
        if ($groupBy === 'week') {
            list($year, $week) = explode('-', $period);
            $startOfWeek = \Carbon\Carbon::create($year)->setISODate($year, $week)->startOfWeek();
            $endOfWeek = \Carbon\Carbon::create($year)->setISODate($year, $week)->endOfWeek();
            return $startOfWeek->format('d/m/Y') . ' - ' . $endOfWeek->format('d/m/Y');
        }
        return \Carbon\Carbon::createFromFormat('Y-m', $period)->format('M/Y');
    }
}
