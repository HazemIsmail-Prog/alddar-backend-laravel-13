<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Comment extends Model
{
    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'type',
        'body',
        'media_disk',
        'media_path',
        'duration_seconds',
    ];

    // create booted
    protected static function booted()
    {
        static::creating(function ($comment) {
            $comment->created_by = request()->user()->id;
        });
    }

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
        ];
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comment_readers', 'comment_id', 'user_id')
            ->withTimestamps();
    }

    protected $appends = ['media_url'];

    public function getMediaUrlAttribute(): ?string
    {
        if ($this->type === 'voice' && $this->media_disk === 'public' && $this->media_path) {
            return Storage::disk($this->media_disk)->url($this->media_path);
        }
        return null;
    }
}
