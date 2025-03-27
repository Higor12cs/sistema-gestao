<?php

namespace App\Services;

use App\Models\ChartAccount;
use App\Models\Configuration;
use App\Models\Order;
use App\Models\Product;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function calculateOrderDetails(array $items, float $orderDiscount = 0, float $orderFees = 0): array
    {
        $totalCost = 0;
        $totalPrice = 0;
        $processedItems = [];
        $existingItemIds = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $quantity = $item['quantity'];
            $unitCost = $product->cost;
            $unitPrice = $item['unit_price'];
            $itemDiscount = ($item['discount'] ?? 0);
            $itemFees = ($item['fees'] ?? 0);

            $itemTotalCost = $unitCost * $quantity;
            $itemTotalPrice = ($unitPrice * $quantity) - $itemDiscount + $itemFees;

            $totalCost += $itemTotalCost;
            $totalPrice += $itemTotalPrice;

            $itemData = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'discount' => $itemDiscount,
                'fees' => $itemFees,
                'total_cost' => $itemTotalCost,
                'total_price' => $itemTotalPrice,
                'created_by' => Auth::id(),
            ];

            if (isset($item['id'])) {
                $existingItemIds[] = $item['id'];
                $processedItems[] = array_merge(['id' => $item['id']], $itemData);
            } else {
                $processedItems[] = $itemData;
            }
        }

        $finalTotalPrice = $totalPrice - $orderDiscount + $orderFees;

        return [
            'total_cost' => $totalCost,
            'total_price' => $finalTotalPrice,
            'items' => $processedItems,
            'existing_item_ids' => $existingItemIds,
        ];
    }

    public function createOrder(array $data): Order
    {
        $orderData = $this->calculateOrderDetails(
            $data['items'],
            ($data['discount'] ?? 0),
            ($data['fees'] ?? 0)
        );

        return DB::transaction(function () use ($data, $orderData) {
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'seller_id' => $data['seller_id'] ?? null,
                'issue_date' => Carbon::parse($data['issue_date'])->format('Y-m-d'),
                'total_cost' => $orderData['total_cost'],
                'discount' => ($data['discount'] ?? 0),
                'fees' => ($data['fees'] ?? 0),
                'total_price' => $orderData['total_price'],
                'observation' => $data['observation'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($orderData['items'] as $item) {
                $order->items()->create($item);
            }

            return $order;
        });
    }

    public function updateOrder(Order $order, array $data): Order
    {
        $orderData = $this->calculateOrderDetails(
            $data['items'],
            ($data['discount'] ?? 0),
            ($data['fees'] ?? 0)
        );

        return DB::transaction(function () use ($order, $data, $orderData) {
            $order->update([
                'customer_id' => $data['customer_id'],
                'seller_id' => $data['seller_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'total_cost' => $orderData['total_cost'],
                'discount' => ($data['discount'] ?? 0),
                'fees' => ($data['fees'] ?? 0),
                'total_price' => $orderData['total_price'],
                'observation' => $data['observation'] ?? null,
            ]);

            $order->items()->whereNotIn('id', $orderData['existing_item_ids'])->delete();

            foreach ($orderData['items'] as $item) {
                if (isset($item['id'])) {
                    $order->items()->where('id', $item['id'])->update($item);
                } else {
                    $order->items()->create($item);
                }
            }

            return $order->fresh(['items', 'createdBy']);
        });
    }

    public function createReceivables(Order $order, array $receivablesData): void
    {
        $totalReceivablesAmount = 0;

        foreach ($receivablesData as $receivable) {
            $totalReceivablesAmount += $receivable['amount'];
        }

        if (abs($totalReceivablesAmount - $order->total_price) > 0.01) {
            throw new \Exception('O valor total dos recebíveis deve ser igual ao valor do pedido.');
        }

        $defaultChartAccountId = ChartAccount::where('default_receivable', true)->value('id');

        if (!$defaultChartAccountId) {
            throw new \Exception('Não foi possível encontrar uma conta padrão para lançamentos de pedidos.');
        }

        DB::transaction(function () use ($order, $receivablesData, $defaultChartAccountId) {
            foreach ($receivablesData as $receivableData) {
                Receivable::create([
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'chart_account_id' => $defaultChartAccountId,
                    'payment_method_id' => $receivableData['payment_method_id'],
                    'is_manual' => false,
                    'issue_date' => $order->issue_date,
                    'due_date' => $receivableData['due_date'],
                    'total_amount' => $receivableData['amount'],
                    'paid_amount' => 0,
                    'fees' => 0,
                    'discount' => 0,
                    'remaining_amount' => $receivableData['amount'],
                    'status' => 'pending',
                    'description' => $receivableData['description'] ?? 'Recebível gerado automaticamente',
                    'created_by' => Auth::id(),
                ]);
            }
        });

        app(StockService::class)->processStockForOrder($order);
    }

    public function deleteOrder(Order $order): void
    {
        $hasPayments = false;

        foreach ($order->receivables as $receivable) {
            if ($receivable->paid_amount > 0) {
                $hasPayments = true;
                break;
            }
        }

        if ($hasPayments) {
            throw new \Exception('Não é possível excluir um pedido com recebíveis que possuem baixas.');
        }

        DB::transaction(function () use ($order) {
            $order->receivables()->delete();
            $order->items()->delete();
            $order->delete();
        });
    }
}
