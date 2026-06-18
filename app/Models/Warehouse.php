<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['can_update', 'can_delete', 'name'];

    public function getNameAttribute(): object
    {
        return (object) [
            'en' => $this->name_en,
            'ar' => $this->name_ar,
        ];
    }

    public function getCanUpdateAttribute()
    {
        return request()->user()->hasPermission('warehouses_update');
    }

    public function getCanDeleteAttribute()
    {
        return request()->user()->hasPermission('warehouses_delete');
    }

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($warehouse) {
            $warehouse->created_by = request()->user()->id;
        });
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockTransfersFrom()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    public function stockTransfersTo()
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_warehouse');
    }

}
