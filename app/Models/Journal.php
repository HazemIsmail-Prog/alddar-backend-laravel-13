<?php

namespace App\Models;

use App\Http\Traits\SyncMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory, SyncMany;

    protected $guarded = [];

    protected $appends = ['can_update', 'can_delete'];

    public function getCanUpdateAttribute()
    {
        return request()->user()->hasPermission('journals_update');
    }

    public function getCanDeleteAttribute()
    {
        return request()->user()->hasPermission('journals_delete');
    }

    protected static function booted()
    {
        static::creating(function ($journal) {
            $journal->created_by = request()->user()->id;
        });
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function entries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    protected function totalDebit(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }

    protected function totalCredit(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }
}
