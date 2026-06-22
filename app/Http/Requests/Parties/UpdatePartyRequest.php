<?php

namespace App\Http\Requests\Parties;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->boolean('is_client') 
            ? request()->user()->hasPermission('clients_update') 
            : ($this->boolean('is_vendor') 
                ? request()->user()->hasPermission('vendors_update') 
                : false);
    }

    public function rules(): array
    {
        return [
            'is_client' => 'required|boolean',
            'is_vendor' => 'required|boolean',
            'name' => 'required|string|max:255',
            'status' => 'nullable|string',
            'locations' => 'nullable|array',
            'locations.*' => 'nullable|array',
            'locations.*.id' => 'nullable|exists:locations,id',
            'locations.*.locationable_id' => 'nullable|numeric',
            'locations.*.locationable_type' => 'nullable|string',
            'locations.*.label' => 'nullable|string',
            'locations.*.country' => 'nullable|string',
            'locations.*.city' => 'nullable|string',
            'locations.*.area' => 'required|string',
            'locations.*.block' => 'required|string',
            'locations.*.street' => 'nullable|string',
            'locations.*.avenue' => 'nullable|string',
            'locations.*.building' => 'nullable|string',
            'locations.*.floor' => 'nullable|string',
            'locations.*.flat' => 'nullable|string',
            'locations.*.paci_number' => 'nullable|string',
            'locations.*.google_map_link' => 'nullable|string',
            'locations.*.notes' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*' => 'nullable|array',
            'phones.*.id' => 'nullable|exists:phones,id',
            'phones.*.phoneable_id' => 'nullable|numeric',
            'phones.*.phoneable_type' => 'nullable|string',
            'phones.*.label' => 'nullable|string',
            'phones.*.country_code' => 'required|string',
            'phones.*.number' => 'required|string',
            'phones.*.extension' => 'nullable|string',
            'phones.*.notes' => 'nullable|string',
        ];
    }
}
