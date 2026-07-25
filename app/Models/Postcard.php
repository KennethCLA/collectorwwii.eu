<?php

namespace App\Models;

use App\Models\Concerns\HasMainImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Postcard extends Model
{
    use HasMainImage, SoftDeletes;

    protected $fillable = [
        'country_id',
        'year',
        'postcard_type_id',
        'nominal_value_id',
        'currency_id',
        'valuation_image_id',
        'colour_id',
        'print_type_id',
        'occasion',
        'michel_number',
        'date_of_issue',
        'front_image',
        'special_features',
        'stamp_text',
        'stamp_date',
        'stamp_location',
        'unstamped',
        'stamped',
        'special_stamp',
        'perforation',
        'for_sale',
        'selling_price',
        'purchase_date',
        'purchasing_price',
        'current_value',
        'width',
        'height',
        'print_run',
        'location_id',
        'location_detail',
        'personal_remarks',
        'condition',
        'sold_at',
        'sold_price',
    ];

    protected $casts = [
        'for_sale' => 'boolean',
        'unstamped' => 'boolean',
        'stamped' => 'boolean',
        'special_stamp' => 'boolean',
        'perforation' => 'boolean',
        'purchase_date' => 'date',
        'selling_price' => 'decimal:2',
        'purchasing_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'sold_at' => 'date',
        'sold_price' => 'decimal:2',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function postcardType()
    {
        return $this->belongsTo(PostcardType::class, 'postcard_type_id');
    }

    public function nominalValue()
    {
        return $this->belongsTo(NominalValue::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
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

    public function getCardTitleAttribute(): string
    {
        $parts = array_filter([
            $this->country?->name,
            $this->year,
        ]);

        return implode(' · ', $parts) ?: 'Postcard #'.$this->id;
    }
}
