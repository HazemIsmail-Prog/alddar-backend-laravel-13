<?php

namespace App\Http\Requests\Warehouses;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('warehouses_create');
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255|unique:warehouses,name_en',
            'name_ar' => 'required|string|max:255|unique:warehouses,name_ar',
            'type' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ];
    }
}
