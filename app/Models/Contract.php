<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Contract extends Model
{
    protected $guarded = [];

    protected $appends = [
        'can_update', 
        'can_delete',
        'formatted_contract_date',
        'formatted_contract_expiration_date',
        'formatted_compressor_warranty_start_date',
        'formatted_compressor_warranty_end_date'
    ];

    protected $casts = [
        'contract_date' => 'date',
        'contract_expiration_date' => 'date',
        'compressor_warranty_start_date' => 'date',
        'compressor_warranty_end_date' => 'date',
    ];

    protected function contractValue(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }

    protected function getFormattedContractDateAttribute()
    {
        return $this->contract_date ? Carbon::parse($this->contract_date)->format('d/m/Y') : null;
    }

    protected function getFormattedContractExpirationDateAttribute()
    {
        return $this->contract_expiration_date ? Carbon::parse($this->contract_expiration_date)->format('d/m/Y') : null;
    }
    
    protected function getFormattedCompressorWarrantyStartDateAttribute()
    {
        return $this->compressor_warranty_start_date ? Carbon::parse($this->compressor_warranty_start_date)->format('d/m/Y') : null;
    }
    
    

    protected function getFormattedCompressorWarrantyEndDateAttribute()
    {
        return $this->compressor_warranty_end_date ? Carbon::parse($this->compressor_warranty_end_date)->format('d/m/Y') : null;
    }

    public function getCanUpdateAttribute()
    {
        return request()->user()->hasPermission('contracts_update');
    }

    public function getCanDeleteAttribute()
    {
        return request()->user()->hasPermission('contracts_delete');
    }

    protected static function booted()
    {
        static::creating(function ($contract) {
            $contract->created_by = request()->user()->id;
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
