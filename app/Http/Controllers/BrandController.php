<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Brands/Index', [
            'brands' => $brands,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Brands/Create');
    }

    public function store(BrandRequest $request)
    {
        Brand::create($request->validated());

        return to_route('brands.index')->with('success', 'Marca criada com sucesso!');
    }

    // public function show(Brand $brand)
    // {
    //     //
    // }

    public function edit(Brand $brand)
    {
        return inertia('Brands/Edit', [
            'brand' => $brand,
        ]);
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());

        return to_route('brands.index')->with('success', 'Marca atualizada com sucesso!');
    }

    public function destroy(Brand $brand)
    {
        // TODO: Check if the brand has any related data before deleting it

        abort(403, 'Forbidden');

        $brand->delete();

        return to_route('brands.index')->with('success', 'Marca excluída com sucesso!');
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $sections = Brand::whereIn('id', $ids)
                ->get(['id', 'name']);

            return response()->json([
                'data' => $sections,
            ]);
        }

        $query = $request->search ?? '';

        $sections = Brand::query()
            ->where('name', 'ilike', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name']);

        return response()->json([
            'data' => $sections,
        ]);
    }
}
