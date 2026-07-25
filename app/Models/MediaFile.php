<?php

// app/Models/MediaFile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model
{
    protected $fillable = [
        'disk',
        'path',
        'thumb_path',
        'mime_type',
        'size',
        'original_name',
        'checksum',
        'collection',
        'is_main',
        'sort_order',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        $path = ltrim((string) $this->path, '/');

        return Storage::disk($this->disk)->url($path);
    }

    public function thumbUrl(): string
    {
        if (! $this->thumb_path) {
            return $this->url();
        }

        $path = ltrim((string) $this->thumb_path, '/');

        return Storage::disk($this->disk)->url($path);
    }

    public function getUrlAttribute(): string
    {
        return $this->url();
    }

    public function getThumbUrlAttribute(): string
    {
        return $this->thumbUrl();
    }

    public function isPdf(): bool
    {
        return ($this->mime_type === 'application/pdf') || str_ends_with(strtolower($this->path), '.pdf');
    }
}
