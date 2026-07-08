<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('contracts_update');
    }

    public function rules(): array
    {
        return [
            'contract_number' => 'required|string|max:255|unique:contracts,contract_number,'.$this->input('id'),
            'contract_type' => 'required|string|max:255',
            'contract_value' => 'required|numeric',
            'contract_date' => 'required|date',
            'contract_expiration_date' => 'required|date',
            'compressor_warranty_start_date' => 'required|date',
            'compressor_warranty_end_date' => 'required|date',
            'parts_status' => 'required|numeric',
            'contract_status' => 'required|string|max:255',
            'contract_payment_status' => 'required|string|max:255',
            'contract_details' => 'required|string|max:255',
            'created_by' => 'required|exists:users,id',
            'party_id' => 'required|exists:parties,id',
        ];
    }

}
