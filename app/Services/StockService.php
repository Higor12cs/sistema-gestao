<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function initializeStock(Product $product, ?float $initialQuantity = 0): Stock
    {
        return DB::transaction(function () use ($product, $initialQuantity) {
            $stock = Stock::create([
                'product_id' => $product->id,
                'quantity' => $initialQuantity,
                'created_by' => Auth::id(),
            ]);

            if ($initialQuantity > 0) {
                $this->registerMovement(
                    $stock,
                    'in',
                    'initial',
                    null,
                    0,
                    $initialQuantity,
                    $initialQuantity,
                    'ESTOQUE INICIAL'
                );
            }

            return $stock;
        });
    }

    public function registerMovement(
        Stock $stock,
        string $type,
        string $sourceType,
        ?string $sourceId,
        float $previousQuantity,
        float $quantity,
        float $newQuantity,
        ?string $notes = null
    ): StockMovement {
        return StockMovement::create([
            'stock_id' => $stock->id,
            'product_id' => $stock->product_id,
            'type' => $type,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'previous_quantity' => $previousQuantity,
            'quantity' => $quantity,
            'new_quantity' => $newQuantity,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    public function addToStock(Stock $stock, float $quantity, string $sourceType, ?string $sourceId, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($stock, $quantity, $sourceType, $sourceId, $notes) {
            $previousQuantity = $stock->quantity;
            $newQuantity = $previousQuantity + $quantity;

            $stock->update([
                'quantity' => $newQuantity,
            ]);

            return $this->registerMovement(
                $stock,
                'in',
                $sourceType,
                $sourceId,
                $previousQuantity,
                $quantity,
                $newQuantity,
                $notes
            );
        });
    }

    public function removeFromStock(Stock $stock, float $quantity, string $sourceType, ?string $sourceId, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($stock, $quantity, $sourceType, $sourceId, $notes) {
            $previousQuantity = $stock->quantity;
            $newQuantity = $previousQuantity - $quantity;

            $stock->update([
                'quantity' => $newQuantity,
            ]);

            return $this->registerMovement(
                $stock,
                'out',
                $sourceType,
                $sourceId,
                $previousQuantity,
                $quantity,
                $newQuantity,
                $notes
            );
        });
    }

    public function adjustStock(Stock $stock, float $newQuantity, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($stock, $newQuantity, $notes) {
            $previousQuantity = $stock->quantity;
            $difference = $newQuantity - $previousQuantity;
            $type = $difference >= 0 ? 'in' : 'out';
            $quantity = abs($difference);

            $stock->update([
                'quantity' => $newQuantity,
            ]);

            return $this->registerMovement(
                $stock,
                $type,
                'adjustment',
                null,
                $previousQuantity,
                $quantity,
                $newQuantity,
                $notes
            );
        });
    }

    public function processStockForPurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $stock = $this->getOrCreateStockForProduct($item->product);
                $this->addToStock(
                    $stock,
                    $item->quantity,
                    'purchase',
                    $purchase->id,
                    'ENTRADA POR COMPRA #'.str_pad($purchase->id, 6, '0', STR_PAD_LEFT)
                );
            }
        });
    }

    public function processStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stock = $this->getOrCreateStockForProduct($item->product);
                $this->removeFromStock(
                    $stock,
                    $item->quantity,
                    'order',
                    $order->id,
                    'SAIDA POR PEDIDO #'.str_pad($order->id, 6, '0', STR_PAD_LEFT)
                );
            }
        });
    }

    public function getOrCreateStockForProduct(Product $product): Stock
    {
        $stock = $product->stock;

        if (! $stock) {
            $stock = $this->initializeStock($product);
        }

        return $stock;
    }
}
