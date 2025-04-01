<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Group;
use App\Models\OrderItem;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;

class ProductAbcReportController extends Controller
{
    public function generate(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $analysisType = $request->get('analysis_type', 'value'); // value or quantity
        $brandId = $request->get('brand_id');
        $sectionId = $request->get('section_id');
        $groupId = $request->get('group_id');

        // Construir a query base para obter os dados necessários
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

        // Aplicar filtros
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

        // Obter todos os produtos com valores
        $products = $query->get();

        // Ordenar os produtos com base no tipo de análise
        if ($analysisType === 'value') {
            $products = $products->sortByDesc('total_value');
        } else {
            $products = $products->sortByDesc('total_quantity');
        }

        // Calcular totais
        $totalValue = $products->sum('total_value');
        $totalQuantity = $products->sum('total_quantity');

        // Calcular os percentuais acumulados e classificar em A, B, C
        $accumulatedPercentage = 0;
        $classifiedProducts = [];

        foreach ($products as $product) {
            $percentage = ($analysisType === 'value')
                ? ($product->total_value / $totalValue) * 100
                : ($product->total_quantity / $totalQuantity) * 100;

            $accumulatedPercentage += $percentage;

            // Classificar em A, B ou C
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

        // Totais por classificação
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

        // Preparar percentuais para cada classe
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

        // Obter detalhes dos filtros aplicados
        $filterDetails = [
            'brand' => $brandId ? Brand::find($brandId)?->name : null,
            'section' => $sectionId ? Section::find($sectionId)?->name : null,
            'group' => $groupId ? Group::find($groupId)?->name : null,
        ];

        $html = view('reports.product-abc', [
            'products' => $classifiedProducts,
            'totalsByClass' => $totalsByClass,
            'totalValue' => $totalValue,
            'totalQuantity' => $totalQuantity,
            'totalProducts' => count($classifiedProducts),
            'analysisType' => $analysisType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterDetails' => $filterDetails,
        ])->render();

        $pdf = $this->generatePdf($html);

        $analysisTypeName = $analysisType === 'value' ? 'Valor' : 'Quantidade';
        $filename = "RelatorioCurvaABC_Produtos_{$analysisTypeName}";
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
        return Browsershot::html($html)
            ->setNodeBinary('/usr/bin/node')
            ->setNpmBinary('/usr/bin/npm')
            ->noSandbox()
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(120)
            ->margins(10, 10, 10, 10)
            ->format('A4')
            ->pdf();
    }
}
