<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    // protected $appends = ['can_update', 'can_delete'];

    public function getCanUpdateAttribute()
    {
        return request()->user()->hasPermission('chart_of_accounts_update');
    }

    public function getCanDeleteAttribute()
    {
        return request()->user()->hasPermission('chart_of_accounts_delete');
    }

    protected $casts = [
        'level' => 'integer',
        'is_leaf' => 'boolean',
        'is_system_account' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($chartOfAccount) {
            $chartOfAccount->created_by = request()->user()->id;
        });
    }

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'account_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bank_account_id');
    }

    protected function currentBalance(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }

    protected function openingBalance(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }
}
