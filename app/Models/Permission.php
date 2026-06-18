<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $guarded = [];

    protected $appends = ['can_update', 'can_delete', 'name'];

    public function getNameAttribute(): object
    {
        return (object) [
            'ar' => $this->name_ar,
            'en' => $this->name_en,
        ];
    }

    public function getCanUpdateAttribute()
    {
        return request()->user()->hasPermission('permissions_update');
    }

    public function getCanDeleteAttribute()
    {
        return request()->user()->hasPermission('permissions_delete');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

}
