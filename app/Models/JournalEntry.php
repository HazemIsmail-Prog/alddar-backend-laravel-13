<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }
}
