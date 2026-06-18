<?php

namespace App\Services;

use App\Models\StockLevel;

class StockMovementService
{
    public static function getInvoiceStockMovementsData(array $items, string $movement_type, string $transaction_type) : array
    {
        // get the sum of quantity only for the items that have warehouse id and product id
        $sumOfQuantityByWarehouseIdAndProductId = [];
        foreach ($items as $item) {
            if ($item['warehouse_id'] && $item['product_id']) {
                $key = $item['warehouse_id'] . ':' . $item['product_id'];
                if (!isset($sumOfQuantityByWarehouseIdAndProductId[$key])) {
                    $sumOfQuantityByWarehouseIdAndProductId[$key] = 0;
                }
                $sumOfQuantityByWarehouseIdAndProductId[$key] += $item['quantity'];
            }
        }

        $stockMovementsData = [];

        // if there is no sum of quantity by warehouse id and product id, return empty array
        // this is because we don't need to create stock movements for items that don't have warehouse id and product id
        if (empty($sumOfQuantityByWarehouseIdAndProductId)) {
            return [];
        }

        // create stock movements for the items that have warehouse id and product id
        foreach ($sumOfQuantityByWarehouseIdAndProductId as $key => $quantity) {
            
            // get the quantity before for the item
            $quantityBefore = StockLevel::where('warehouse_id', explode(':', $key)[0])->where('product_id', explode(':', $key)[1])->first()->quantity ?? 0;


            // calculate the quantity after for the item
            $quantityAfter = match ($movement_type) {
                'in' => $quantityBefore + $quantity,
                'out' => $quantityBefore - $quantity,
                default => throw new \Exception('Invalid movement type'),
            };

            // if the quantity after is less than 0, throw an exception
            if ($quantityAfter < 0) {
                throw new \Exception('Quantity after is less than 0');
            }

            // create stock movement for the item
            $stockMovementsData[] = [
                'warehouse_id' => explode(':', $key)[0],
                'product_id' => explode(':', $key)[1],
                'quantity' => $quantity,
                'movement_type' => $movement_type,
                'transaction_type' => $transaction_type,
            ];
        }

        // return the stock movements data
        return $stockMovementsData;
    }

    public static function getStockTransferStockMovementsData(array $items, int $source_warehouse_id, int $destination_warehouse_id) : array
    {
        // get the sum of quantity only for the items that have warehouse id and product id
        $sumOfQuantityByProductId = [];
        foreach ($items as $item) {
            $key = $item['product_id'];
            if (!isset($sumOfQuantityByProductId[$key])) {
                $sumOfQuantityByProductId[$key] = 0;
            }
            $sumOfQuantityByProductId[$key] += $item['quantity'];
        }

        $stockMovementsData = [];

        // create stock movements for the items that have warehouse id and product id
        foreach ($sumOfQuantityByProductId as $key => $quantity) {
            
            // create stock movement for the item
            $stockMovementsData[] = [
                'warehouse_id' => $source_warehouse_id,
                'product_id' => $key,
                'quantity' => $quantity,
                'movement_type' => 'out',
                'transaction_type' => 'transfer',
            ];

            $stockMovementsData[] = [
                'warehouse_id' => $destination_warehouse_id,
                'product_id' => $key,
                'quantity' => $quantity,
                'movement_type' => 'in',
                'transaction_type' => 'transfer',
            ];

        }

        // return the stock movements data
        return $stockMovementsData;
    }

}
