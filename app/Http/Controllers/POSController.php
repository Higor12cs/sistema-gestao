<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Receivable;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $defaultCustomer = Customer::first();

        $featuredProducts = Product::query()
            ->orderBy('name')
            ->limit(20)
            ->get();

        return inertia('POS/Index', [
            'defaultCustomer' => $defaultCustomer,
            'featuredProducts' => $featuredProducts,
        ]);
    }

    public function processeSale(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.fees' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'fees' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,card,pix,installment',
            'installments' => 'nullable|integer|min:1|max:12',
            'observation' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Criar o pedido usando o serviço existente
            $orderData = [
                'customer_id' => $request->customer_id,
                'seller_id' => Auth::id(), // O usuário logado é o vendedor
                'issue_date' => now()->toDateString(),
                'items' => $request->items,
                'discount' => $request->discount ?? 0,
                'fees' => $request->fees ?? 0,
                'observation' => $request->observation,
            ];

            $order = $this->orderService->createOrder($orderData);

            // Criar contas a receber baseado no método de pagamento
            $this->createAccountsReceivable($order, $request);

            DB::commit();

            return redirect()->route('POS.index')->with('success', [
                'message' => 'Venda realizada com sucesso!',
                'order_id' => $order->id,
                'total' => $order->total_price,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Erro ao processar a venda: '.$e->getMessage(),
            ])->withInput();
        }
    }

    protected function createAccountsReceivable(Order $order, Request $request)
    {
        $paymentMethod = $request->payment_method;
        $totalAmount = $order->total_price;

        switch ($paymentMethod) {
            case 'cash':
            case 'pix':
                // Pagamento à vista - criar uma conta já paga
                Receivable::create([
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'description' => "Venda POS #{$order->id}",
                    'amount' => $totalAmount,
                    'due_date' => now(),
                    'status' => 'paid',
                    'payment_date' => now(),
                    'payment_method' => $paymentMethod,
                    'created_by' => Auth::id(),
                ]);
                break;

            case 'card':
                // Cartão - criar conta a receber para o próximo dia útil
                Receivable::create([
                    'customer_id' => $order->customer_id,
                    'order_id' => $order->id,
                    'description' => "Venda POS #{$order->id} - Cartão",
                    'amount' => $totalAmount,
                    'due_date' => now()->addDays(1),
                    'status' => 'pending',
                    'payment_method' => 'card',
                    'created_by' => Auth::id(),
                ]);
                break;

            case 'installment':
                // Parcelado - criar múltiplas contas a receber
                $installments = $request->installments ?? 1;
                $installmentAmount = round($totalAmount / $installments, 2);
                $remainingAmount = $totalAmount;

                for ($i = 1; $i <= $installments; $i++) {
                    // Para a última parcela, ajustar o valor para não ter diferença de centavos
                    $currentAmount = ($i === $installments) ? $remainingAmount : $installmentAmount;
                    $remainingAmount -= $currentAmount;

                    Receivable::create([
                        'customer_id' => $order->customer_id,
                        'order_id' => $order->id,
                        'description' => "Venda POS #{$order->id} - Parcela {$i}/{$installments}",
                        'amount' => $currentAmount,
                        'due_date' => now()->addMonths($i - 1),
                        'status' => 'pending',
                        'payment_method' => 'installment',
                        'installment_number' => $i,
                        'total_installments' => $installments,
                        'created_by' => Auth::id(),
                    ]);
                }
                break;
        }
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 20);

        $products = Product::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'price')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function getProductByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');

        $product = Product::where('sku', $barcode)
            ->select('id', 'name', 'price')
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function printReceipt($orderId)
    {
        $order = Order::with(['customer', 'items.product', 'seller'])
            ->findOrFail($orderId);

        return inertia('POS/Receipt', [
            'order' => $order,
        ]);
    }
}
