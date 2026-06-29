<?php

namespace App\Http\Controllers;

use App\Models\StockLevel;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockLevelController
{
    protected array $with = ['product', 'warehouse'];

    protected array $searchable = ['product.name', 'warehouse.name', 'quantity', 'average_cost', 'last_cost'];

    public function index(Request $request)
    {
        $stockLevels = StockLevel::query()
            ->with($this->with)
            ->when($request->search, function ($query, $search) {
                $query->whereAny($this->searchable, 'like', '%'.$search.'%');
            })
            ->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json($stockLevels);
    }

    public function getStockLevel(Product $product, Warehouse $warehouse)
    {
        $stockLevel = StockLevel::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        return response()->json($stockLevel);
    }
}
