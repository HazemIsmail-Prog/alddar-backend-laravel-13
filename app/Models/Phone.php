<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $appends = ['full_phone'];
    public function phoneable()
    {
        return $this->morphTo();
    }

    public function getFullPhoneAttribute()
    {
        return $this->country_code . ' ' . $this->number . ' ' . $this->extension;
    }
}
