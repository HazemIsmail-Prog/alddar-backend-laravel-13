<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        
        $itemsData = [];
        foreach ($this->input('items', []) as $item) {
            $itemsData[] = [
                'id' => $item['id'],
                'description' => $item['description'],
                'product_id' => $item['product_id'],
                'warehouse_id' => $item['warehouse_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_amount' => $item['discount_amount'],
                'total_amount' => $item['unit_price'] * $item['quantity'] - $item['discount_amount'],
            ];
        }

        $this->merge([
            'items' => $itemsData,
        ]);
    }

    public function rules(): array
    {
        return [
            'department_id' => 'nullable|exists:departments,id',
            'technician_id' => 'nullable|exists:users,id',
            'location_id' => 'required|exists:locations,id',
            'phone_id' => 'required|exists:phones,id',
            'order_type' => 'required|in:sales,purchase',
            'party_id' => 'required|exists:parties,id',
            'order_date' => 'required|date',
            'status_id' => 'required|exists:order_statuses,id',
            'notes' => 'nullable|string|max:255',
            'is_confirmed_to_dispatch' => 'required|boolean',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:order_items,id',
            'items.*.description' => 'nullable|required_if:items.*.product_id,null|string|max:255',
            'items.*.product_id' => 'nullable|required_if:items.*.description,null|exists:products,id',
            'items.*.warehouse_id' => 'nullable|required_unless:items.*.product_id,null|prohibited_if:items.*.product_id,null|exists:warehouses,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'required|numeric|min:0',
            'items.*.total_amount' => 'required|numeric|min:0',
        ];
    }
}
