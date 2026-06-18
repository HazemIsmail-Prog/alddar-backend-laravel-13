<?php

namespace App\Http\Requests\Departments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('departments_update');
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255|unique:departments,name_en,'.$this->input('id'),
            'name_ar' => 'required|string|max:255|unique:departments,name_ar,'.$this->input('id'),
            'is_active' => 'required|boolean',
            'is_service_department' => 'required|boolean',
        ];
    }
}
