<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['can_update', 'can_delete', 'name'];

    public function getNameAttribute(): object
    {
        return (object) [
            'ar' => $this->name_ar,
            'en' => $this->name_en,
        ];
    }

    protected $casts = [
        'is_active' => 'boolean',
        'is_service_department' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($department) {
            $department->created_by = request()->user()->id;
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getCanUpdateAttribute()
    {
        return request()->user()->hasPermission('departments_update');
    }

    public function getCanDeleteAttribute()
    {
        return request()->user()->hasPermission('departments_delete');
    }
}
