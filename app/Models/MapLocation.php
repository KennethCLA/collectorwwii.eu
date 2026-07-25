<?php

namespace App\Models;

use App\Models\Concerns\HasMainImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapLocation extends Model
{
    use HasMainImage, SoftDeletes;

    protected $fillable = [
        'coordinates',
        'name',
        'description',
    ];

    public function media(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'attachable');
    }

    public function images(): MorphMany
    {
        return $this->media()
            ->where('collection', 'images')
            ->orderByDesc('is_main')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function files(): MorphMany
    {
        return $this->media()
            ->where('collection', 'files')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function mainImage(): MorphOne
    {
        return $this->morphOne(MediaFile::class, 'attachable')
            ->where('collection', 'images')
            ->where('is_main', 1);
    }

    public function latitude(): ?float
    {
        $parts = array_map('trim', explode(',', (string) $this->coordinates));
        if (count($parts) !== 2) {
            return null;
        }

        return is_numeric($parts[0]) ? (float) $parts[0] : null;
    }

    public function longitude(): ?float
    {
        $parts = array_map('trim', explode(',', (string) $this->coordinates));
        if (count($parts) !== 2) {
            return null;
        }

        return is_numeric($parts[1]) ? (float) $parts[1] : null;
    }
}
