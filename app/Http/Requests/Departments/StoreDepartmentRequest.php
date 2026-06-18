<?php

namespace App\Http\Requests\Departments;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('departments_create');
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255|unique:departments,name_en',
            'name_ar' => 'required|string|max:255|unique:departments,name_ar',
            'is_active' => 'required|boolean',
            'is_service_department' => 'required|boolean',
        ];
    }
}
