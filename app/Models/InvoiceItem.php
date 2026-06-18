<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }

    protected function discountAmount(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }

    protected function totalPrice(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }
}
