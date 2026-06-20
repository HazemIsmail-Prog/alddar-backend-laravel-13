<?php

namespace App\Http\Requests\Permissions;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('permissions_create');
    }

    public function rules(): array
    {
        return [
            'value' => 'required|string|max:255|unique:permissions,value',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ];
    }
    
}
