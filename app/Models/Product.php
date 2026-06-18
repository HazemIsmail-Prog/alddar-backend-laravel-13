<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
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
        return request()->user()->hasPermission('products_update');
    }

    public function getCanDeleteAttribute()
    {
        return request()->user()->hasPermission('products_delete');
    }

    protected $casts = [
        'is_purchasable' => 'boolean',
        'is_sellable' => 'boolean',
        'track_inventory' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            $product->created_by = request()->user()->id;
        });
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function morphedStockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }

    public function stockTransferItems()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function stockAdjustmentItems()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    protected function sellingPrice(): Attribute
    {
        return Attribute::make(
            get: fn (int|float $value) => $value / 1000,
            set: fn (int|float $value) => $value * 1000,
        );
    }

}
