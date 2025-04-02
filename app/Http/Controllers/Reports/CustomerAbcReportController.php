<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class CustomerAbcReportController extends Controller
{
    public function generate(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $analysisType = $request->get('analysis_type', 'value');

        $query = Order::query()
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select(
                'customers.id',
                'customers.sequential_id',
                'customers.first_name',
                'customers.last_name',
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('SUM(orders.total_price) as total_value')
            )
            ->groupBy('customers.id', 'customers.first_name', 'customers.last_name');

        if ($startDate && $endDate) {
            $query->whereBetween('orders.issue_date', [$startDate, $endDate]);
        }

        $customers = $query->get();

        if ($analysisType === 'value') {
            $customers = $customers->sortByDesc('total_value');
        } else {
            $customers = $customers->sortByDesc('order_count');
        }

        $totalValue = $customers->sum('total_value');
        $totalOrders = $customers->sum('order_count');

        $accumulatedPercentage = 0;
        $classifiedCustomers = [];

        foreach ($customers as $customer) {
            $percentage = ($analysisType === 'value')
                ? ($customer->total_value / $totalValue) * 100
                : ($customer->order_count / $totalOrders) * 100;

            $accumulatedPercentage += $percentage;

            $classification = 'C';
            if ($accumulatedPercentage <= 80) {
                $classification = 'A';
            } elseif ($accumulatedPercentage <= 95) {
                $classification = 'B';
            }

            $classifiedCustomers[] = [
                'id' => $customer->id,
                'sequential_id' => $customer->sequential_id,
                'name' => $customer->first_name.' '.$customer->last_name,
                'order_count' => $customer->order_count,
                'total_value' => $customer->total_value,
                'percentage' => $percentage,
                'accumulated_percentage' => $accumulatedPercentage,
                'classification' => $classification,
            ];
        }

        $totalsByClass = [
            'A' => [
                'count' => collect($classifiedCustomers)->where('classification', 'A')->count(),
                'value' => collect($classifiedCustomers)->where('classification', 'A')->sum('total_value'),
                'orders' => collect($classifiedCustomers)->where('classification', 'A')->sum('order_count'),
            ],
            'B' => [
                'count' => collect($classifiedCustomers)->where('classification', 'B')->count(),
                'value' => collect($classifiedCustomers)->where('classification', 'B')->sum('total_value'),
                'orders' => collect($classifiedCustomers)->where('classification', 'B')->sum('order_count'),
            ],
            'C' => [
                'count' => collect($classifiedCustomers)->where('classification', 'C')->count(),
                'value' => collect($classifiedCustomers)->where('classification', 'C')->sum('total_value'),
                'orders' => collect($classifiedCustomers)->where('classification', 'C')->sum('order_count'),
            ],
        ];

        foreach (['A', 'B', 'C'] as $class) {
            $totalsByClass[$class]['percent_count'] = count($classifiedCustomers) > 0
                ? ($totalsByClass[$class]['count'] / count($classifiedCustomers)) * 100
                : 0;

            $totalsByClass[$class]['percent_value'] = $totalValue > 0
                ? ($totalsByClass[$class]['value'] / $totalValue) * 100
                : 0;

            $totalsByClass[$class]['percent_orders'] = $totalOrders > 0
                ? ($totalsByClass[$class]['orders'] / $totalOrders) * 100
                : 0;
        }

        $html = view('reports.customer-abc', [
            'customers' => $classifiedCustomers,
            'totalsByClass' => $totalsByClass,
            'totalValue' => $totalValue,
            'totalOrders' => $totalOrders,
            'totalCustomers' => count($classifiedCustomers),
            'analysisType' => $analysisType,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->render();

        $pdf = $this->generatePdf($html);

        $analysisTypeName = $analysisType === 'value' ? 'Valor' : 'Quantidade';
        $filename = "RelatorioCurvaABC_Clientes_{$analysisTypeName}";
        if ($startDate && $endDate) {
            $filename .= "_{$startDate}_{$endDate}";
        }
        $filename .= '.pdf';

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }

    private function generatePdf($html)
    {
        $browsershot = Browsershot::html($html)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(120)
            ->margins(10, 10, 10, 10)
            ->format('A4');

        if (config('app.env') === 'production') {
            $browsershot->setNodeBinary('/usr/bin/node')
                ->setNpmBinary('/usr/bin/npm')
                ->noSandbox();
        }

        return $browsershot->pdf();
    }
}
