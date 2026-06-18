<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('products_create');
    }

    public function rules(): array
    {
        return [
            'department_id' => 'nullable|exists:departments,id',
            'category_id' => 'required|exists:categories,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'unit_of_measure' => 'required|string|in:piece,kg,liter,meter,set,box,pair',
            'is_active' => 'nullable|boolean',
            'is_purchasable' => 'nullable|boolean',
            'is_sellable' => 'nullable|boolean',
            'track_inventory' => 'nullable|boolean',
            'opening_quantity' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'income_account_id' => 'nullable|exists:chart_of_accounts,id',
            'expense_account_id' => 'nullable|exists:chart_of_accounts,id',
            'inventory_account_id' => 'nullable|exists:chart_of_accounts,id',
        ];
    }
}
