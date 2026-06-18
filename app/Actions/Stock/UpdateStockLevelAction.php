<?php

namespace App\Actions\Stock;

use App\Models\StockLevel;
use App\Models\StockMovement;

final class UpdateStockLevelAction
{

    public function handle(): void
    {

        // Get all stock movements from the database
        $stockMovements = StockMovement::all();

        // Group stock movements by product_id and warehouse_id
        $stockMovementsByProductAndWarehouse = $stockMovements
            ->groupBy(function ($item) {
                return $item->product_id . '-' . $item->warehouse_id;
            });

        $stockLevels = [];

        foreach ($stockMovementsByProductAndWarehouse as $key => $movements) {
            [$productId, $warehouseId] = explode('-', $key);

            // Sum 'in' and 'out' movement types separately
            $sumIn = $movements->where('movement_type', 'in')->sum('quantity');
            $sumOut = $movements->where('movement_type', 'out')->sum('quantity');

            $quantity = $sumIn - $sumOut;

            $stockLevels[] = [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => $quantity,
            ];
        }


        // empty stock levels table
        StockLevel::truncate();

        // upsert stock levels
        StockLevel::upsert($stockLevels, ['product_id', 'warehouse_id'], ['quantity']);
    }
}
