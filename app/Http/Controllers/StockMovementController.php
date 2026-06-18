<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController
{
    protected array $with = ['product', 'warehouse', 'reference'];

    protected array $searchable = ['product.name', 'warehouse.name', 'movement_type', 'transaction_type', 'reason'];

    public function index(Request $request)
    {
        $stockMovements = StockMovement::query()
            ->with([
                ...$this->with,
            ])
            ->latest()
            // ->orderBy('created_at', 'desc')
            ->when($request->search, function ($query, $search) {
                $query->whereAny($this->searchable, 'like', '%'.$search.'%');
            })
            ->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json($stockMovements);
    }
}
