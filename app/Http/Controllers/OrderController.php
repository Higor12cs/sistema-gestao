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
        // Define datas padrão (últimos 7 dias) se não forem fornecidas
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->subDays(7)->format('Y-m-d');
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->format('Y-m-d');

        $orders = Order::query()
            ->with(['customer', 'receivables', 'seller', 'createdBy'])
            ->when($request->filled('sequential_id'), function ($query) use ($request) {
                $query->where('sequential_id', $request->sequential_id);
            })
            ->when($request->filled('customer_id'), function ($query) use ($request) {
                $query->where('customer_id', $request->customer_id);
            })
            ->when(true, function ($query) use ($startDate, $endDate) {
                // Sempre aplica o filtro de data, usando os valores padrão se necessário
                if ($startDate && $endDate) {
                    $query->whereBetween('issue_date', [
                        $startDate,
                        $endDate,
                    ]);
                } elseif ($startDate) {
                    $query->where('issue_date', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('issue_date', '<=', $endDate);
                }
            })
            ->when($request->filled('seller_id'), function ($query) use ($request) {
                $query->where('seller_id', $request->seller_id);
            })
            ->when($request->filled('created_by'), function ($query) use ($request) {
                $query->where('created_by', $request->created_by);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->status;
                if ($status === 'pending') {
                    $query->whereDoesntHave('receivables');
                } elseif ($status === 'finalized') {
                    $query->whereHas('receivables');
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $hasResults = true;

        $selectedCustomer = $request->filled('customer_id')
            ? Customer::find($request->customer_id)
            : null;

        $selectedSeller = $request->filled('seller_id')
            ? Seller::find($request->seller_id)
            : null;

        $selectedCreatedBy = $request->filled('created_by')
            ? User::find($request->created_by)
            : null;

        return inertia('Orders/Index', [
            'orders' => $orders,
            'filters' => array_merge(
                $request->only(['sequential_id', 'customer_id', 'seller_id', 'created_by', 'status']),
                ['start_date' => $startDate, 'end_date' => $endDate]
            ),
            'hasResults' => $hasResults,
            'selectedCustomer' => $selectedCustomer,
            'selectedSeller' => $selectedSeller,
            'selectedCreatedBy' => $selectedCreatedBy,
        ]);
    }

    // Restante dos métodos permanece o mesmo
    public function create()
    {
        return inertia('Orders/Create', [
            'order' => null,
        ]);
    }

    public function store(OrderStoreRequest $request)
    {
        $order = $this->orderService->createOrder($request->validated());

        return to_route('orders.create-receivables', $order->sequential_id)
            ->with('success', 'Pedido criado com sucesso.');
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
            return to_route('orders.show', $order->sequential_id)
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
            return to_route('orders.show', $order->sequential_id)
                ->with('error', 'Pedidos com recebíveis não podem ser editados.');
        }

        $this->orderService->updateOrder($order, $request->validated());

        return to_route('orders.index')->with('success', 'Pedido atualizado com sucesso.');
    }

    public function destroy(Order $order)
    {
        try {
            $this->orderService->deleteOrder($order);

            return to_route('orders.index')
                ->with('success', 'Pedido excluído com sucesso.');
        } catch (\Exception $e) {
            return to_route('orders.index')
                ->with('error', $e->getMessage());
        }
    }

    public function createReceivables(Order $order)
    {
        if ($order->hasReceivables()) {
            return to_route('orders.show', $order->sequential_id)
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

            return to_route('orders.show', $order->sequential_id)
                ->with('success', 'Recebíveis criados com sucesso.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function print(Order $order, Request $request)
    {
        $type = $request->query('type', 'a4');
        if (!in_array($type, ['a4', 'thermal'])) {
            $type = 'a4';
        }

        $order = Order::with(['customer', 'items.product', 'createdBy', 'receivables.paymentMethod'])
            ->findOrFail($order->id);

        $view = $type === 'thermal' ? 'reports.orders.order-thermal' : 'reports.orders.order-a4';
        $html = view($view, compact('order'))->render();

        $browsershot = Browsershot::html($html)
            ->setNodeBinary('/usr/bin/node')
            ->setNpmBinary('/usr/bin/npm')
            ->noSandbox()
            ->showBackground();

        if ($type === 'thermal') {
            $browsershot->paperSize(80, 297)
                ->margins(1, 1, 1, 1)
                ->deviceScaleFactor(1.5)
                ->scale(1.0);
        } else {
            $browsershot->format('A4');
        }

        $pdf = $browsershot->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline');
    }
}
