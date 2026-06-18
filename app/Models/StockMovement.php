<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['formatted_created_at', 'formatted_created_at_time', 'reference_number'];

    public function reference()
    {
        return $this->morphTo();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // protected function unitCost(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (int|float $value) => $value / 1000,
    //         set: fn (int|float $value) => $value * 1000,
    //     );
    // }

    // protected function totalCost(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (int|float $value) => $value / 1000,
    //         set: fn (int|float $value) => $value * 1000,
    //     );
    // }

    public function getFormattedCreatedAtAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getFormattedCreatedAtTimeAttribute()
    {
        return Carbon::parse($this->created_at)->format('H:i:s');
    }

    public function getReferenceNumberAttribute(): ?string
    {
        if (! $this->relationLoaded('reference')) {
            return null;
        }

        $ref = $this->reference;

        if (! $ref) {
            return null;
        }

        if ($ref instanceof Invoice) {
            return $ref->invoice_number;
        }

        if ($ref instanceof StockTransfer) {
            return $ref->transfer_number;
        }

        return null;
    }
}
