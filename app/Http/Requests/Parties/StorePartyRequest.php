<?php

namespace App\Http\Requests\Parties;

use Illuminate\Foundation\Http\FormRequest;

class StorePartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->boolean('is_client') 
            ? request()->user()->hasPermission('clients_create') 
            : ($this->boolean('is_vendor') 
                ? request()->user()->hasPermission('vendors_create') 
                : false);
    }

    public function rules(): array
    {
        return [
            'is_client' => 'required|boolean',
            'is_vendor' => 'required|boolean',
            'name' => 'required|string|max:255',
            'status' => 'nullable|string',
            'locations' => 'required|array|min:1',
            'locations.*' => 'required|array|min:1',
            'locations.*.id' => 'nullable|exists:locations,id',
            'locations.*.locationable_id' => 'nullable|numeric',
            'locations.*.locationable_type' => 'nullable|string',
            'locations.*.label' => 'required|string',
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
            'phones' => 'required|array|min:1',
            'phones.*' => 'required|array|min:1',
            'phones.*.id' => 'nullable|exists:phones,id',
            'phones.*.phoneable_id' => 'nullable|numeric',
            'phones.*.phoneable_type' => 'nullable|string',
            'phones.*.label' => 'required|string',
            'phones.*.country_code' => 'required|string',
            'phones.*.number' => 'required|string',
            'phones.*.extension' => 'nullable|string',
            'phones.*.notes' => 'nullable|string',
        ];
    }
}
