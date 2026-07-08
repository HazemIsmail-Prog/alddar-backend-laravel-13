<?php

namespace App\Http\Requests\Contracts;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->hasPermission('contracts_create');
    }
    protected function prepareForValidation(): void
    {

        $contractNumber = 'CON-' . now()->format('YmdHis').random_int(1000, 9999);
        $contract_payment_status = 'pending';

        $this->merge([
            'contract_number' => $contractNumber,
            'contract_payment_status' => $contract_payment_status,
        ]);
    }

    public function rules(): array
    {
        return [
            'party_id' => 'required|exists:parties,id',
            'contract_number' => 'required|string|max:255|unique:contracts,contract_number',
            'contract_type' => 'required|string|max:255',
            'contract_value' => 'required|numeric',
            'contract_date' => 'required|date|before:contract_expiration_date',
            'contract_expiration_date' => 'required|date|after:contract_date',
            'compressor_warranty_start_date' => 'required|date|before:compressor_warranty_end_date',
            'compressor_warranty_end_date' => 'required|date|after:compressor_warranty_start_date',
            'parts_status' => 'required|numeric',
            'contract_status' => 'required|string|max:255',
            'contract_payment_status' => 'required|string|max:255',
            'contract_details' => 'required|string|max:255',
        ];
    }
    
}
