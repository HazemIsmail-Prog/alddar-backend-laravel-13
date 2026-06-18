<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('roles_update');
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255|unique:roles,name_en,'.$this->input('id'),
            'name_ar' => 'required|string|max:255|unique:roles,name_ar,'.$this->input('id'),
            'permissions' => 'nullable|array|exclude',
            'permissions.*' => 'nullable|exists:permissions,id|exclude',
        ];
    }
}
