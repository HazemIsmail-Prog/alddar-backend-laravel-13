<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $appends = ['full_location'];

    public function locationable()
    {
        return $this->morphTo();
    }

    public function getFullLocationAttribute()
    {
        return $this->label . ' ' . $this->country . ' ' . $this->city . ' ' . $this->area . ' ' . $this->block . ' ' . $this->street . ' ' . $this->avenue . ' ' . $this->building . ' ' . $this->floor 
        ;
    }
}
