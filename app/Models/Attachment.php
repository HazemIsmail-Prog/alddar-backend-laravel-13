<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'created_by',
        'description',
        'file_disk',
        'file_path',
        'original_name',
        'mime_type',
    ];

    protected static function booted()
    {
        static::creating(function ($attachment) {
            $attachment->created_by = request()->user()->id;
        });
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
