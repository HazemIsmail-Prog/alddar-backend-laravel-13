<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('roles_create');
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'permissions' => 'nullable|array|exclude',
            'permissions.*' => 'nullable|exists:permissions,id|exclude',
        ];
    }
}
