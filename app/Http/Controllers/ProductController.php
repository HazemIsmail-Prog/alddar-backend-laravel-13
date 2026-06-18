<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Requests\Products\StoreProductRequest;
use App\Models\Warehouse;
use App\Actions\Stock\UpdateStockLevelAction;

class ProductController
{
    protected array $with = ['category', 'department'];

    protected array $searchable = ['name_en', 'name_ar'];

    public function index(Request $request)
    {
        if(!request()->user()->hasPermission('products_view')) {
            return response()->json([
                'message' => [
                    'ar' =>'ليس لديك صلاحية لعرض المنتجات', 
                    'en' => 'You do not have permission to view products'
                ],
            ], 403);
        }

        $query = Product::query()
            ->with($this->with)
            ->orderBy('id', 'desc');
        
        if($request->has('search')) {
            $query->whereAny($this->searchable, 'like', '%'.$request->search.'%');
        }
        
        $products = $query->paginate($request->has('per_page') ? $request->integer('per_page') : 15);

        return response()->json([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'next_page_url' => $products->nextPageUrl(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'can_create' => request()->user()->hasPermission('products_create'),
        ]);
    }

    public function store(StoreProductRequest $request)
    {

        $defaultWarehouse = Warehouse::where('is_default', true)->first();
        if (!$defaultWarehouse) {
            return response()->json(['message' => 'Default warehouse not found'], 500);
        }
        DB::beginTransaction();
        try {
            $product = Product::create($request->validated());
            $product->morphedStockMovements()->create([
                'product_id' => $product->id,
                'warehouse_id' => $defaultWarehouse->id,
                'quantity' => $request->opening_quantity,
                'movement_type' => 'in',
                'transaction_type' => 'opening_stock',
            ]);

            (new UpdateStockLevelAction())->handle();
            DB::commit();
            return response()->json($product->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $product->update($request->validated());

            // get current stock movement for this product
            $currentStockMovement = $product->stockMovements()->first();
            $currentStockMovementCreatedAt = $currentStockMovement->created_at;
            $currentStockMovementWarehouseId = $currentStockMovement->warehouse_id;

            // delete stock movements
            $product->morphedStockMovements()->delete();
            
            // create new stock movements
            $product->morphedStockMovements()->create([
                'product_id' => $product->id,
                'warehouse_id' => $currentStockMovementWarehouseId,
                'quantity' => $request->opening_quantity,
                'movement_type' => 'in',
                'transaction_type' => 'opening_stock',
                'created_at' => $currentStockMovementCreatedAt,
                'updated_at' => now(),
            ]);
            // update stock level
            (new UpdateStockLevelAction())->handle();
            DB::commit();
            return response()->json($product->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Product $product)
    {
        if(!request()->user()->hasPermission('products_delete')) {
            return response()->json(['message' => 'ليس لديك صلاحية لحذف المنتجات'], 403);
        }

        DB::beginTransaction();
        try {
            $product->morphedStockMovements()->delete();
            $product->stockLevels()->delete();
            $product->delete();
            DB::commit();
            return response()->json($product);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
