<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $stocks = Stock::with(['product'])
            ->when($request->filled('search'), function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Stocks/Index', [
            'stocks' => $stocks,
            'filters' => $request->only(['search']),
        ]);
    }

    public function adjust(Stock $stock)
    {
        $stock->load('product');

        return inertia('Stocks/Adjust', [
            'stock' => $stock,
        ]);
    }

    public function storeAdjustment(StockAdjustmentRequest $request, Stock $stock)
    {
        $newQuantity = $request->input('new_quantity');
        $notes = $request->input('notes');

        $this->stockService->adjustStock($stock, $newQuantity, $notes);

        return to_route('stocks.index')->with('success', 'Estoque ajustado com sucesso!');
    }
}
