<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('users_update');
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$this->input('id'),
            'civil_id' => 'required|numeric|digits:12|unique:users,civil_id,'.$this->input('id'),
            'password' => 'nullable|string|min:3|exclude',
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
