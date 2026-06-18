<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchingHistory extends Model
{
    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($dispatchingHistory) {
            $dispatchingHistory->created_by = request()->user()->id;
            $dispatchingHistory->status_id = $dispatchingHistory->order->status_id;
            $dispatchingHistory->technician_id = $dispatchingHistory->order->technician_id;
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
