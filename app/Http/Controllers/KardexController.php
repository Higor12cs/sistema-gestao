<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class KardexController extends Controller
{
    public function index(Request $request)
    {
        $product = null;
        $movements = null;

        if ($request->filled('product_id')) {
            $product = Product::findOrFail($request->input('product_id'));

            $movements = StockMovement::query()
                ->where('product_id', $product->id)
                ->when($request->filled('start_date'), function ($query) use ($request) {
                    $query->whereDate('created_at', '>=', $request->input('start_date'));
                })
                ->when($request->filled('end_date'), function ($query) use ($request) {
                    $query->whereDate('created_at', '<=', $request->input('end_date'));
                })
                ->with(['createdBy'])
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();
        }

        return inertia('Kardex/Index', [
            'product' => $product,
            'movements' => $movements,
            'filters' => $request->only(['product_id', 'start_date', 'end_date']),
        ]);
    }
}
