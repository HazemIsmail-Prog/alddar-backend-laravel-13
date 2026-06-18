<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\SyncMany;

class Order extends Model
{
    use HasFactory, SyncMany;

    protected $guarded = [];

    protected $appends = [
        'is_un_invoiced_completed_orders',
    ];

    protected $casts = [
        'is_confirmed_to_dispatch' => 'boolean',
        'is_inprogress' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            $order->created_by = request()->user()->id;
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function phone()
    {
        return $this->belongsTo(Phone::class, 'phone_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'reference');
    }

    public function getIsUnInvoicedCompletedOrdersAttribute()
    {
        return $this->status_id === 6 && $this->invoices->isEmpty();
    }

    public function dispatchingHistories()
    {
        return $this->hasMany(DispatchingHistory::class);
    }

    // public function journals()
    // {
    //     return $this->morphMany(Journal::class, 'reference');
    // }

    // public function journalEntries()
    // {
    //     return $this->morphMany(JournalEntry::class, 'reference');
    // }
}
