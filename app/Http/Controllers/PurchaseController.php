<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchasePayableRequest;
use App\Http\Requests\PurchaseStoreRequest;
use App\Http\Requests\PurchaseUpdateRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->startOfDay()->format('Y-m-d');
        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->endOfDay()->format('Y-m-d');

        $query = Purchase::query()->with(['supplier', 'payables', 'createdBy']);

        if ($request->filled('sequential_id')) {
            $query->where('sequential_id', $request->sequential_id);
        } else {
            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('issue_date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('issue_date', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('issue_date', '<=', $endDate);
            }

            if ($request->filled('created_by')) {
                $query->where('created_by', $request->created_by);
            }

            if ($request->filled('status')) {
                $status = $request->status;
                if ($status === 'pending') {
                    $query->whereDoesntHave('payables');
                } elseif ($status === 'finalized') {
                    $query->whereHas('payables');
                }
            }
        }

        $purchases = $query->latest()
            ->paginate(10)
            ->withQueryString();

        $selectedSupplier = $request->filled('supplier_id') ? Supplier::find($request->supplier_id) : null;
        $selectedCreatedBy = $request->filled('created_by') ? User::find($request->created_by) : null;

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'filters' => array_merge(
                $request->only(['sequential_id', 'supplier_id', 'created_by', 'status']),
                ['start_date' => $startDate, 'end_date' => $endDate]
            ),
            'hasResults' => true,
            'selectedSupplier' => $selectedSupplier,
            'selectedCreatedBy' => $selectedCreatedBy,
        ]);
    }

    public function create()
    {
        return Inertia::render('Purchases/Create', [
            'purchase' => null,
        ]);
    }

    public function store(PurchaseStoreRequest $request)
    {
        $purchase = $this->purchaseService->createPurchase($request->validated());

        return to_route('purchases.create-payables', $purchase->sequential_id)->with('success', 'Compra criada com sucesso!');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['items.product', 'createdBy', 'payables.paymentMethod']);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }

    public function edit(Purchase $purchase)
    {
        if ($purchase->hasPayables()) {
            return to_route('purchases.show', $purchase->sequential_id)->with('error', 'Compras com pagáveis não podem ser editadas.');
        }

        $purchase->load(['items.product', 'createdBy']);

        return Inertia::render('Purchases/Edit', [
            'purchase' => $purchase,
        ]);
    }

    public function update(PurchaseUpdateRequest $request, Purchase $purchase)
    {
        if ($purchase->hasPayables()) {
            return to_route('purchases.show', $purchase->sequential_id)->with('error', 'Compras com pagáveis não podem ser editadas.');
        }

        $this->purchaseService->updatePurchase($purchase, $request->validated());

        return to_route('purchases.index')->with('success', 'Compra atualizada com sucesso!');
    }

    public function destroy(Purchase $purchase)
    {
        try {
            $this->purchaseService->deletePurchase($purchase);

            return to_route('purchases.index')->with('success', 'Compra excluída com sucesso!');
        } catch (\Exception $e) {
            return to_route('purchases.index')->with('error', $e->getMessage());
        }
    }

    public function createPayables(Purchase $purchase)
    {
        if ($purchase->hasPayables()) {
            return to_route('purchases.show', $purchase->sequential_id)
                ->with('error', 'Esta compra já possui pagáveis.');
        }

        return Inertia::render('Purchases/CreatePayables', [
            'purchase' => $purchase->load(['supplier']),
        ]);
    }

    public function storePayables(PurchasePayableRequest $request, Purchase $purchase)
    {
        try {
            $payablesData = collect($request->validated()['payables'])
                ->map(function ($payable) {
                    return [
                        'payment_method_id' => $payable['payment_method_id'],
                        'due_date' => $payable['due_date'],
                        'amount' => $payable['amount'],
                        'description' => $payable['description'] ?? null,
                    ];
                })
                ->toArray();

            $this->purchaseService->createPayables($purchase, $payablesData);

            return to_route('purchases.show', $purchase->sequential_id)
                ->with('success', 'Pagáveis criados com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function search(Request $request)
    {
        if ($request->has('ids')) {
            $ids = explode(',', $request->ids);
            $purchases = Purchase::whereIn('id', $ids)->get();

            return response()->json([
                'data' => $purchases->map(function (Purchase $purchase) {
                    return [
                        'id' => $purchase->id,
                        'name' => 'Compra #' . $purchase->sequential_id . ' - ' . $purchase->issue_date->format('d/m/Y'),
                        'total' => $purchase->total_cost,
                    ];
                }),
            ]);
        }

        $query = $request->search ?? '';
        $purchases = Purchase::where('sequential_id', 'ilike', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $purchases->map(function (Purchase $purchase) {
                return [
                    'id' => $purchase->id,
                    'name' => 'Compra #' . $purchase->sequential_id . ' - ' . $purchase->issue_date->format('d/m/Y'),
                    'total' => $purchase->total_cost,
                ];
            }),
        ]);
    }
}
