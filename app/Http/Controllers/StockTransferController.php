<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\StockMovementService;
use App\Actions\Stock\UpdateStockLevelAction;
use App\Http\Requests\StockTransferes\StoreStockTransferRequest;
use App\Http\Requests\StockTransferes\UpdateStockTransferRequest;

class StockTransferController
{
    protected array $with = ['fromWarehouse', 'toWarehouse', 'items.product'];

    public function index(Request $request)
    {
        $query = StockTransfer::query()
            ->with($this->with)
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhereHas('fromWarehouse', fn ($w) => $w->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('toWarehouse', fn ($w) => $w->where('name', 'like', '%'.$search.'%'));
            });
        }

        $stockTransfers = $query->paginate($request->integer('per_page', 15));

        return response()->json(
            [
                'data' => $stockTransfers->items(),
                'current_page' => $stockTransfers->currentPage(),
                'last_page' => $stockTransfers->lastPage(),
                'next_page_url' => $stockTransfers->nextPageUrl(),
                'per_page' => $stockTransfers->perPage(),
                'total' => $stockTransfers->total(),
                'can_create' => request()->user()->hasPermission('stock_transfers_create'),
            ]);
    }

    public function store(StoreStockTransferRequest $request)
    {
        $safe = $request->safe();
        $transferData = $safe->except('items');
        $itemsData = $safe->input('items', []);

        DB::beginTransaction();
        try {

            $transfer = StockTransfer::create($transferData);

            $transfer->items()->createMany($itemsData);

            $stockMovementsData = StockMovementService::getStockTransferStockMovementsData($itemsData, $transferData['from_warehouse_id'], $transferData['to_warehouse_id']);

            if (!empty($stockMovementsData)) {
    
                $transfer->stockMovements()->createMany($stockMovementsData);
                (new UpdateStockLevelAction())->handle();
    
            }

            DB::commit();

            return response()->json($transfer->fresh()->load($this->with));

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }

    public function update(UpdateStockTransferRequest $request, StockTransfer $stockTransfer)
    {
        $safe = $request->safe();
        $transferData = $safe->except('items');
        $itemsData = $safe->input('items', []);


        DB::beginTransaction();
        try {

            $stockTransfer->update($transferData);

            $stockTransfer->syncMany('items', $itemsData);

            $stockTransfer->stockMovements()->delete();

            $stockMovementsData = StockMovementService::getStockTransferStockMovementsData($itemsData, $transferData['from_warehouse_id'], $transferData['to_warehouse_id']);

            if (!empty($stockMovementsData)) {

                foreach ($stockMovementsData as $stockMovement) {
                    $stockMovement['created_at'] = $stockTransfer->created_at;
                    $stockMovement['updated_at'] = now();
                    $stockTransfer->stockMovements()->create($stockMovement);
                }
    
                (new UpdateStockLevelAction())->handle();
    
            }
            DB::commit();
            return response()->json($stockTransfer->fresh()->load($this->with));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(StockTransfer $stockTransfer)
    {
        DB::beginTransaction();
        try {
            $stockTransfer->stockMovements()->delete();
            $stockTransfer->items()->delete();
            $stockTransfer->delete();
            (new UpdateStockLevelAction())->handle();
            DB::commit();
            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
