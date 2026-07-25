<?php

namespace App\Models;

use App\Models\Concerns\HasMainImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Newspaper extends Model
{
    use HasMainImage, SoftDeletes;

    protected $fillable = [
        'series_id',
        'title',
        'publisher',
        'publication_date',
        'description',
        'purchase_date',
        'purchase_price',
        'for_sale',
        'selling_price',
        'notes',
        'condition',
        'sold_at',
        'sold_price',
    ];

    protected $casts = [
        'for_sale' => 'boolean',
        'publication_date' => 'date',
        'purchase_date' => 'date',
        'selling_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sold_at' => 'date',
        'sold_price' => 'decimal:2',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(NewspaperSeries::class);
    }

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

}
