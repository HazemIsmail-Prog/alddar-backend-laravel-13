<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockAdjustmentController
{
    protected array $with = ['warehouse', 'items.product', 'items.productVariant'];

    public function index(Request $request)
    {
        $query = StockAdjustment::query()
            ->with($this->with)
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_number', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%')
                    ->orWhere('adjustment_type', 'like', '%'.$search.'%')
                    ->orWhere('reason', 'like', '%'.$search.'%')
                    ->orWhereHas('warehouse', fn ($w) => $w->where('name', 'like', '%'.$search.'%'));
            });
        }

        return response()->json(
            $query->paginate($request->integer('per_page', 15))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        return DB::transaction(function () use ($validated) {
            $items = $validated['items'];
            unset($validated['items']);
            $adjustment = StockAdjustment::create($validated);
            foreach ($items as $item) {
                $adjustment->items()->create($item);
            }

            return response()->json($adjustment->fresh()->load($this->with));
        });
    }

    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $validated = $request->validate($this->rules($stockAdjustment->id));

        return DB::transaction(function () use ($validated, $stockAdjustment) {
            $items = $validated['items'];
            unset($validated['items']);
            $stockAdjustment->update($validated);
            $stockAdjustment->items()->delete();
            foreach ($items as $item) {
                $stockAdjustment->items()->create($item);
            }

            return response()->json($stockAdjustment->fresh()->load($this->with));
        });
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->delete();

        return response()->noContent();
    }

    protected function rules(?int $adjustmentId = null): array
    {
        return [
            'adjustment_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stock_adjustments', 'adjustment_number')->ignore($adjustmentId),
            ],
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_type' => [
                'required',
                Rule::in(['positive', 'negative', 'damage', 'loss', 'found', 'expired']),
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'approved', 'completed', 'cancelled']),
            ],
            'adjustment_date' => 'required|date',
            'reason' => 'required|string',
            'documents' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'nullable|integer|min:0',
            'items.*.reason' => 'nullable|string',
        ];
    }
}
