<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('users_create');
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users',
            'civil_id' => 'required|numeric|digits:12|unique:users,civil_id',
            'password' => 'required|string|min:8',
            'is_active' => 'required|boolean',
            'is_technician' => 'required|boolean',
            'departments' => 'nullable|array|exclude',
            'departments.*' => 'nullable|exists:departments,id|exclude',
            'permissions' => 'nullable|array|exclude',
            'permissions.*' => 'nullable|exists:permissions,id|exclude',
            'roles' => 'nullable|array|exclude',
            'roles.*' => 'nullable|exists:roles,id|exclude',
            'warehouses' => 'nullable|array|exclude',
            'warehouses.*' => 'nullable|exists:warehouses,id|exclude',
        ];
    }
}
