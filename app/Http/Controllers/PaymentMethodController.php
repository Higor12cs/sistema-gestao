<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentMethodRequest;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('PaymentMethods/Index', [
            'paymentMethods' => $paymentMethods,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('PaymentMethods/Create');
    }

    public function store(PaymentMethodRequest $request)
    {
        PaymentMethod::create($request->validated());

        return to_route('payment-methods.index')->with('success', 'Método de pagamento criado com sucesso!');
    }

    // public function show(PaymentMethod $paymentMethod)
    // {
    //     //
    // }

    public function edit(PaymentMethod $paymentMethod)
    {
        return inertia('PaymentMethods/Edit', [
            'paymentMethod' => $paymentMethod,
        ]);
    }

    public function update(PaymentMethodRequest $request, PaymentMethod $paymentMethod)
    {
        $paymentMethod->update($request->validated());

        return to_route('payment-methods.index')->with('success', 'Método de pagamento atualizado com sucesso!');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        // TODO: Check if the paymentMethod has any related data before deleting it

        abort(403, 'Forbidden');

        $paymentMethod->delete();

        return to_route('payment-methods.index')->with('success', 'Método de pagamento excluído com sucesso!');
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $sections = PaymentMethod::whereIn('id', $ids)
                ->get(['id', 'name']);

            return response()->json([
                'data' => $sections,
            ]);
        }

        $query = $request->search ?? '';

        $sections = PaymentMethod::query()
            ->where('name', 'ilike', "%{$query}%")
            ->limit(5)
            ->get(['id', 'name']);

        return response()->json([
            'data' => $sections,
        ]);
    }
}
