<?php

namespace App\Http\Requests\Warehouses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('warehouses_update');
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255|unique:warehouses,name_en,'.$this->input('id'),
            'name_ar' => 'required|string|max:255|unique:warehouses,name_ar,'.$this->input('id'),
            'type' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ];
    }
}
