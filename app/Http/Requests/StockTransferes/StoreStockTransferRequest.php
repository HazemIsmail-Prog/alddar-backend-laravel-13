<?php

namespace App\Http\Requests\StockTransferes;

use App\Models\StockLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $transferNumber = 'ST-' . now()->format('YmdHis').random_int(1000, 9999);

        $itemsData = [];
        $validationErrors = [];
        if ($this->input('items')) {

            $sum_quantity_by_product = [];
            foreach ($this->input('items', []) as $item) {
                $sum_quantity_by_product[$item['product_id']] = ($sum_quantity_by_product[$item['product_id']] ?? 0) + $item['quantity'];
            }

            foreach ($this->input('items', []) as $index => $item) {
                if ($item['product_id']) {
                    $available_quantity = StockLevel::query()
                        ->where('product_id', $item['product_id'])
                        ->where('warehouse_id', $this->input('from_warehouse_id'))
                        ->first()->quantity ?? 0;

                    if ($available_quantity < $sum_quantity_by_product[$item['product_id']]) {
                        $validationErrors['items.'.$index.'.quantity'] = ['Quantity not available in selected warehouse'];
                    }
                }
                $itemsData[] = [
                    // 'id' => $item['id'] ?? null,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'],
                ];
            }
        }

        if (count($validationErrors) > 0) {
            throw ValidationException::withMessages($validationErrors);
        }

        $this->merge([
            'transfer_number' => $transferNumber,
            'items' => $itemsData,
        ]);
    }

    public function rules(): array
    {
        return [
            'transfer_number' => 'required|string|max:64',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',

            'items' => 'required|array|min:1',
            // 'items.*.id' => 'nullable|exists:stock_transfer_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string',
        ];
    }
}
