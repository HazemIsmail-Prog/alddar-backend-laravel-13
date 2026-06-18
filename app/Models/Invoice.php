<?php

namespace App\Models;

use App\Http\Traits\SyncMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, SyncMany;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->created_by = request()->user()->id;
        });
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function journals()
    {
        return $this->morphMany(Journal::class, 'reference');
    }

    public function journalEntries()
    {
        return $this->morphMany(JournalEntry::class, 'reference');
    }

    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }

    protected function subtotal(): Attribute
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

    protected function amountPaid(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }
}
