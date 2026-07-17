<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $guarded = [];

    public const UNASSIGNED = 1;
    public const HOLDED = 2;
    public const ASSIGNED = 3;
    public const RECEIVED = 4;
    public const ARRIVED = 5;
    public const COMPLETED = 6;
    public const CANCELLED = 7;

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
