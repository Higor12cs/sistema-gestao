<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('legal_name', 'ilike', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Suppliers/Create');
    }

    public function store(SupplierRequest $request)
    {
        Supplier::create($request->validated());

        return to_route('suppliers.index')->with('success', 'Fornecedor criado com sucesso!');
    }

    // public function show(Supplier $supplier)
    // {
    //     //
    // }

    public function edit(Supplier $supplier)
    {
        return inertia('Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated());

        return to_route('suppliers.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }

    public function destroy(Supplier $supplier)
    {
        // TODO: Check if the supplier has any related data before deleting it

        abort(403, 'Forbidden');

        $supplier->delete();

        return to_route('suppliers.index')->with('success', 'Fornecedor excluído com sucesso!');
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $suppliers = Supplier::whereIn('id', $ids)->get();

            return response()->json([
                'data' => $suppliers->map(function (Supplier $supplier) {
                    return [
                        'id' => $supplier->id,
                        'name' => $supplier->first_name.' '.$supplier->last_name,
                    ];
                }),
            ]);
        }

        $query = $request->search ?? '';
        $suppliers = Supplier::where('first_name', 'ilike', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $suppliers->map(function (Supplier $supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->first_name.' '.$supplier->last_name,
                ];
            }),
        ]);
    }
}
