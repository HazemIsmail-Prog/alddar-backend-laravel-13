<?php

namespace App\Http\Requests\Orders;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $departmentId = $this->input('department_id');

        

        $orderNumber = match ($this->order_type) {
            'sales' => 'SO-' . now()->format('YmdHis').random_int(1000, 9999),
            'purchase' => 'PO-' . now()->format('YmdHis').random_int(1000, 9999),
            default => null,
        };

        $firstUnassignedOrderInDepartment = Order::query()
            ->where('department_id', $departmentId)
            ->where('status_id', 1)
            ->orderBy('sort_number', 'asc')
            ->first();

        $sortNumber = $firstUnassignedOrderInDepartment
            ? $firstUnassignedOrderInDepartment->sort_number - 1000
            : 0;

        $itemsData = [];
        if ($this->input('items')) {
            foreach ($this->input('items', []) as $item) {
                $itemsData[] = [
                    'description' => $item['description'],
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'],
                    'total_amount' => $item['unit_price'] * $item['quantity'] - $item['discount_amount'],
                ];
            }
        }

        $this->merge([
            'order_number' => $orderNumber,
            'sort_number' => $sortNumber,
            'items' => $itemsData,
        ]);
    }

    public function rules(): array
    {
        return [
            'order_number' => 'required|string|max:64',
            'sort_number' => 'required|numeric',
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
