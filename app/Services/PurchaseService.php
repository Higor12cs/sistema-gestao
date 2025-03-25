<?php

namespace App\Services;

use App\Models\ChartAccount;
use App\Models\Payable;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function calculatePurchaseDetails(array $items, float $purchaseDiscount = 0, float $purchaseFees = 0): array
    {
        $totalCost = 0;
        $processedItems = [];
        $existingItemIds = [];

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $quantity = $item['quantity'];
            $unitCost = $item['unit_cost'];
            $itemDiscount = ($item['discount'] ?? 0);
            $itemFees = ($item['fees'] ?? 0);

            $itemTotalCost = ($unitCost * $quantity) - $itemDiscount + $itemFees;

            $totalCost += $itemTotalCost;

            $itemData = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'discount' => $itemDiscount,
                'fees' => $itemFees,
                'total_cost' => $itemTotalCost,
                'created_by' => Auth::id(),
            ];

            if (isset($item['id'])) {
                $existingItemIds[] = $item['id'];
                $processedItems[] = array_merge(['id' => $item['id']], $itemData);
            } else {
                $processedItems[] = $itemData;
            }
        }

        $finalTotalCost = $totalCost - $purchaseDiscount + $purchaseFees;

        return [
            'total_cost' => $finalTotalCost,
            'items' => $processedItems,
            'existing_item_ids' => $existingItemIds,
        ];
    }

    public function createPurchase(array $data): Purchase
    {
        $purchaseData = $this->calculatePurchaseDetails(
            $data['items'],
            ($data['discount'] ?? 0),
            ($data['fees'] ?? 0)
        );

        return DB::transaction(function () use ($data, $purchaseData) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'issue_date' => $data['issue_date'],
                'discount' => ($data['discount'] ?? 0),
                'fees' => ($data['fees'] ?? 0),
                'total_cost' => $purchaseData['total_cost'],
                'observation' => $data['observation'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($purchaseData['items'] as $item) {
                $purchase->items()->create($item);

                $product = Product::findOrFail($item['product_id']);
                $product->update([
                    'cost' => $item['unit_cost'],
                ]);
            }

            return $purchase;
        });
    }

    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        $purchaseData = $this->calculatePurchaseDetails(
            $data['items'],
            ($data['discount'] ?? 0),
            ($data['fees'] ?? 0)
        );

        return DB::transaction(function () use ($purchase, $data, $purchaseData) {
            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'issue_date' => $data['issue_date'],
                'discount' => ($data['discount'] ?? 0),
                'fees' => ($data['fees'] ?? 0),
                'total_cost' => $purchaseData['total_cost'],
                'observation' => $data['observation'] ?? null,
            ]);

            $purchase->items()->whereNotIn('id', $purchaseData['existing_item_ids'])->delete();

            foreach ($purchaseData['items'] as $item) {
                if (isset($item['id'])) {
                    $purchase->items()->where('id', $item['id'])->update($item);
                } else {
                    $purchase->items()->create($item);
                }

                $product = Product::findOrFail($item['product_id']);
                $product->update([
                    'cost' => $item['unit_cost'],
                ]);
            }

            return $purchase->fresh(['items', 'createdBy']);
        });
    }

    public function createPayables(Purchase $purchase, array $payablesData): void
    {
        $totalPayablesAmount = 0;

        foreach ($payablesData as $payable) {
            $totalPayablesAmount += $payable['amount'];
        }

        if (abs($totalPayablesAmount - $purchase->total_cost) > 0.01) {
            throw new \Exception('O valor total dos pagáveis deve ser igual ao valor da compra.');
        }

        $defaultChartAccountId = ChartAccount::where('default_purchase', true)->value('id');

        if (!$defaultChartAccountId) {
            throw new \Exception('Não foi possível encontrar uma conta padrão para lançamentos de compras.');
        }

        DB::transaction(function () use ($purchase, $payablesData, $defaultChartAccountId) {
            foreach ($payablesData as $payableData) {
                Payable::create([
                    'purchase_id' => $purchase->id,
                    'chart_account_id' => $defaultChartAccountId,
                    'supplier_id' => $purchase->supplier_id,
                    'payment_method_id' => $payableData['payment_method_id'],
                    'is_manual' => false,
                    'issue_date' => $purchase->issue_date,
                    'due_date' => $payableData['due_date'],
                    'total_amount' => $payableData['amount'],
                    'paid_amount' => 0,
                    'fees' => 0,
                    'discount' => 0,
                    'remaining_amount' => $payableData['amount'],
                    'status' => 'pending',
                    'description' => $payableData['description'] ?? 'Pagável gerado automaticamente',
                    'created_by' => Auth::id(),
                ]);
            }
        });

        app(StockService::class)->processStockForPurchase($purchase);
    }

    public function deletePurchase(Purchase $purchase): void
    {
        $hasPayments = false;

        foreach ($purchase->payables as $payable) {
            if ($payable->paid_amount > 0) {
                $hasPayments = true;
                break;
            }
        }

        if ($hasPayments) {
            throw new \Exception('Não é possível excluir uma compra com pagáveis que possuem baixas.');
        }

        DB::transaction(function () use ($purchase) {
            $purchase->payables()->delete();
            $purchase->items()->delete();
            $purchase->delete();
        });
    }
}
