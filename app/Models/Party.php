<?php

namespace App\Models;

use App\Http\Traits\SyncMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory, SyncMany;
    
    protected $guarded = [];

    protected $appends = ['can_update', 'can_delete'];

    protected $casts = [
        'is_vendor' => 'boolean',
        'is_client' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($party) {
            $party->created_by = request()->user()->id;
        });
    }

    public function locations()
    {
        return $this->morphMany(Location::class, 'locationable');
    }

    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'party_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getCanUpdateAttribute()
    {
        if($this->is_client) {
            return request()->user()->hasPermission('clients_update');
        }
        if($this->is_vendor) {
            return request()->user()->hasPermission('vendors_update');
        }
        if($this->is_client && $this->is_vendor) {
            return request()->user()->hasPermission('clients_update') && request()->user()->hasPermission('vendors_update');
        }
        return false;
    }

    public function getCanDeleteAttribute()
    {
        if($this->is_client) {
            return request()->user()->hasPermission('clients_delete');
        }
        if($this->is_vendor) {
            return request()->user()->hasPermission('vendors_delete');
        }
        if($this->is_client && $this->is_vendor) {
            return request()->user()->hasPermission('clients_delete') && request()->user()->hasPermission('vendors_delete');
        }
        return false;
    }
}
