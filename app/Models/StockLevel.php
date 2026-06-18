<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLevel extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // average cost
    // protected function averageCost(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (int|float $value) => $value / 1000,
    //         set: fn (int|float $value) => $value * 1000,
    //     );
    // }

    // // last cost
    // protected function lastCost(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (int|float $value) => $value / 1000,
    //         set: fn (int|float $value) => $value * 1000,
    //     );
    // }
}
