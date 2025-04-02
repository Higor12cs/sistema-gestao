<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('first_name', 'ilike', "%{$search}%")
                    ->orWhere('last_name', 'ilike', "%{$search}%")
                    ->orWhere('legal_name', 'ilike', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return inertia('Customers/Index', [
            'customers' => $customers,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create()
    {
        return inertia('Customers/Create');
    }

    public function store(CustomerRequest $request)
    {
        Customer::create($request->validated());

        return to_route('customers.index')->with('success', 'Cliente criado com sucesso!');
    }

    // public function show(Customer $customer)
    // {
    //     //
    // }

    public function edit(Customer $customer)
    {
        return inertia('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return to_route('customers.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Customer $customer)
    {
        // TODO: Check if the customer has any related data before deleting it
        return to_route('customers.index')->with('error', 'Funcionalidade não implementada.');

        abort(403, 'Forbidden');

        $customer->delete();

        return to_route('customers.index')->with('success', 'Cliente excluído com sucesso!');
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $customers = Customer::whereIn('id', $ids)->get();

            return response()->json([
                'data' => $customers->map(function (Customer $customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                    ];
                }),
            ]);
        }

        $query = $request->search ?? '';
        $customers = Customer::query()
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('first_name', 'ilike', "%{$query}%")
                    ->orWhere('last_name', 'ilike', "%{$query}%")
                    ->orWhere('legal_name', 'ilike', "%{$query}%");
            })
            ->where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => $customers->map(function (Customer $customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                ];
            }),
        ]);
    }
}
