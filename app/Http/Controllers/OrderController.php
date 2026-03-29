<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderReceivableRequest;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? $request->start_date
            : Carbon::now()->startOfMonth()->startOfDay()->format('Y-m-d');

        $endDate = $request->filled('end_date')
            ? $request->end_date
            : Carbon::now()->endOfMonth()->endOfDay()->format('Y-m-d');

        $query = Order::query()->with(['customer', 'receivables', 'seller', 'createdBy']);

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        } else {
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('issue_date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $query->where('issue_date', '>=', $startDate);
            } elseif ($endDate) {
                $query->where('issue_date', '<=', $endDate);
            }

            if ($request->filled('seller_id')) {
                $query->where('seller_id', $request->seller_id);
            }

            if ($request->filled('created_by')) {
                $query->where('created_by', $request->created_by);
            }

            if ($request->filled('status')) {
                $status = $request->status;
                if ($status === 'pending') {
                    $query->whereDoesntHave('receivables');
                } elseif ($status === 'finalized') {
                    $query->whereHas('receivables');
                }
            }
        }

        $orders = $query->latest()
            ->paginate(10)
            ->withQueryString();

        $selectedCustomer = $request->filled('customer_id') ? Customer::find($request->customer_id) : null;
        $selectedSeller = $request->filled('seller_id') ? Seller::find($request->seller_id) : null;
        $selectedCreatedBy = $request->filled('created_by') ? User::find($request->created_by) : null;

        return inertia('Orders/Index', [
            'orders' => $orders,
            'filters' => array_merge(
                $request->only(['id', 'customer_id', 'seller_id', 'created_by', 'status']),
                ['start_date' => $startDate, 'end_date' => $endDate]
            ),
            'hasResults' => true,
            'selectedCustomer' => $selectedCustomer,
            'selectedSeller' => $selectedSeller,
            'selectedCreatedBy' => $selectedCreatedBy,
        ]);
    }

    public function create()
    {
        return inertia('Orders/Create', [
            'order' => null,
        ]);
    }

    public function store(OrderStoreRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated());

        return to_route('orders.create-receivables', $order->id)
            ->with('success', 'Pedido criado com sucesso!');
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'createdBy', 'receivables.paymentMethod']);

        return inertia('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function edit(Order $order)
    {
        if ($order->hasReceivables()) {
            return to_route('orders.show', $order->id)
                ->with('error', 'Pedidos com recebíveis não podem ser editados.');
        }

        $order->load(['items.product', 'createdBy']);

        return inertia('Orders/Edit', [
            'order' => $order,
        ]);
    }

    public function update(OrderUpdateRequest $request, Order $order)
    {
        if ($order->hasReceivables()) {
            return to_route('orders.show', $order->id)
                ->with('error', 'Pedidos com recebíveis não podem ser editados.');
        }

        $this->orderService->updateOrder($order, $request->validated());

        return to_route('orders.index')->with('success', 'Pedido atualizado com sucesso!');
    }

    public function destroy(Order $order)
    {
        try {
            $this->orderService->deleteOrder($order);

            return to_route('orders.index')
                ->with('success', 'Pedido excluído com sucesso!');
        } catch (\Exception $e) {
            return to_route('orders.index')
                ->with('error', $e->getMessage());
        }
    }

    public function createReceivables(Order $order)
    {
        if ($order->hasReceivables()) {
            return to_route('orders.show', $order->id)
                ->with('error', 'Este pedido já possui recebíveis.');
        }

        return inertia('Orders/CreateReceivables', [
            'order' => $order->load(['customer']),
        ]);
    }

    public function storeReceivables(OrderReceivableRequest $request, Order $order)
    {
        try {
            $receivablesData = collect($request->validated()['receivables'])
                ->map(function ($receivable) {
                    return [
                        'payment_method_id' => $receivable['payment_method_id'],
                        'due_date' => $receivable['due_date'],
                        'amount' => $receivable['amount'],
                        'description' => $receivable['description'] ?? null,
                    ];
                })
                ->toArray();

            $this->orderService->createReceivables($order, $receivablesData);

            return to_route('orders.show', $order->id)
                ->with('success', 'Recebíveis criados com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function print(Order $order, Request $request)
    {
        $type = $request->query('type', 'a4');
        if (! in_array($type, ['a4', 'thermal'])) {
            $type = 'a4';
        }

        $order = Order::with(['customer', 'items.product', 'createdBy', 'receivables.paymentMethod'])
            ->findOrFail($order->id);

        $view = $type === 'thermal' ? 'prints.orders.thermal' : 'prints.orders.a4';
        $html = view($view, compact('order'))->render();

        $browsershot = Browsershot::html($html)
            ->showBackground()
            ->waitUntilNetworkIdle();

        if ($type === 'thermal') {
            $browsershot->paperSize(80, 297)
                ->margins(0, 0, 0, 0)
                ->deviceScaleFactor(1.5)
                ->scale(1.0);
        } else {
            $browsershot->format('A4')
                ->margins(10, 10, 10, 10);
        }

        if (config('app.env') === 'production') {
            $browsershot->setNodeBinary('/usr/bin/node')
                ->setNpmBinary('/usr/bin/npm')
                ->noSandbox();
        }

        $pdf = $browsershot->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline');
    }
}
