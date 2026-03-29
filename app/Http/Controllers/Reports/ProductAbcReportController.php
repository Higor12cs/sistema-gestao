<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Group;
use App\Models\OrderItem;
use App\Models\Section;
use App\Services\Reports\BaseReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAbcReportController extends Controller
{
    protected BaseReportService $reportService;

    public function __construct(BaseReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function generate(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $analysisType = $request->get('analysis_type', 'value');
        $brandId = $request->get('brand_id');
        $sectionId = $request->get('section_id');
        $groupId = $request->get('group_id');

        $query = OrderItem::query()
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('groups', 'products.group_id', '=', 'groups.id')
            ->leftJoin('sections', 'groups.section_id', '=', 'sections.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.sku',
                'brands.name as brand_name',
                'groups.name as group_name',
                'sections.name as section_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.total_price) as total_value')
            )
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'brands.name',
                'groups.name',
                'sections.name'
            );

        if ($startDate && $endDate) {
            $query->whereBetween('orders.issue_date', [$startDate, $endDate]);
        }

        if ($brandId) {
            $query->where('products.brand_id', $brandId);
        }

        if ($sectionId) {
            $query->where('sections.id', $sectionId);
        }

        if ($groupId) {
            $query->where('products.group_id', $groupId);
        }

        $products = $query->get();

        if ($analysisType === 'value') {
            $products = $products->sortByDesc('total_value');
        } else {
            $products = $products->sortByDesc('total_quantity');
        }

        $totalValue = $products->sum('total_value');
        $totalQuantity = $products->sum('total_quantity');

        $accumulatedPercentage = 0;
        $classifiedProducts = [];

        foreach ($products as $product) {
            $percentage = ($analysisType === 'value')
                ? ($product->total_value / $totalValue) * 100
                : ($product->total_quantity / $totalQuantity) * 100;

            $accumulatedPercentage += $percentage;

            $classification = 'C';
            if ($accumulatedPercentage <= 80) {
                $classification = 'A';
            } elseif ($accumulatedPercentage <= 95) {
                $classification = 'B';
            }

            $classifiedProducts[] = [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'sku' => $product->sku,
                'brand_name' => $product->brand_name,
                'group_name' => $product->group_name,
                'section_name' => $product->section_name,
                'total_quantity' => $product->total_quantity,
                'total_value' => $product->total_value,
                'percentage' => $percentage,
                'accumulated_percentage' => $accumulatedPercentage,
                'classification' => $classification,
            ];
        }

        $totalsByClass = [
            'A' => [
                'count' => collect($classifiedProducts)->where('classification', 'A')->count(),
                'value' => collect($classifiedProducts)->where('classification', 'A')->sum('total_value'),
                'quantity' => collect($classifiedProducts)->where('classification', 'A')->sum('total_quantity'),
            ],
            'B' => [
                'count' => collect($classifiedProducts)->where('classification', 'B')->count(),
                'value' => collect($classifiedProducts)->where('classification', 'B')->sum('total_value'),
                'quantity' => collect($classifiedProducts)->where('classification', 'B')->sum('total_quantity'),
            ],
            'C' => [
                'count' => collect($classifiedProducts)->where('classification', 'C')->count(),
                'value' => collect($classifiedProducts)->where('classification', 'C')->sum('total_value'),
                'quantity' => collect($classifiedProducts)->where('classification', 'C')->sum('total_quantity'),
            ],
        ];

        foreach (['A', 'B', 'C'] as $class) {
            $totalsByClass[$class]['percent_count'] = count($classifiedProducts) > 0
                ? ($totalsByClass[$class]['count'] / count($classifiedProducts)) * 100
                : 0;

            $totalsByClass[$class]['percent_value'] = $totalValue > 0
                ? ($totalsByClass[$class]['value'] / $totalValue) * 100
                : 0;

            $totalsByClass[$class]['percent_quantity'] = $totalQuantity > 0
                ? ($totalsByClass[$class]['quantity'] / $totalQuantity) * 100
                : 0;
        }

        $filterDetails = [
            'brand' => $brandId ? Brand::find($brandId)?->name : null,
            'section' => $sectionId ? Section::find($sectionId)?->name : null,
            'group' => $groupId ? Group::find($groupId)?->name : null,
        ];

        $analysisTypeName = $analysisType === 'value' ? 'Valor' : 'Quantidade';
        $filename = "RelatorioCurvaABC_Produtos_{$analysisTypeName}";
        if ($startDate && $endDate) {
            $filename .= "_{$startDate}_{$endDate}";
        }
        $filename .= '.pdf';

        return $this->reportService->streamPdf('reports.abc.products.abc', [
            'products' => $classifiedProducts,
            'totalsByClass' => $totalsByClass,
            'totalValue' => $totalValue,
            'totalQuantity' => $totalQuantity,
            'totalProducts' => count($classifiedProducts),
            'analysisType' => $analysisType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterDetails' => $filterDetails,
        ], $filename, 'landscape');
    }
}
