<?php

namespace App\Models;

use App\Models\Concerns\HasMainImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stamp extends Model
{
    use HasMainImage, SoftDeletes;

    protected $fillable = [
        'country_id',
        'currency_id',
        'nominal_value_id',
        'type_id',
        'designer_id',
        'colour_id',
        'print_type_id',
        'watermark_id',
        'gum_id',
        'perforation_id',
        'printing_house_id',
        'year',
        'michel_number',
        'yvert_tellier_number',
        'date_of_issue',
        'occasion',
        'illustration',
        'special_features',
        'mnh',
        'hinged',
        'postmarked',
        'special_postmark',
        'postmark_date',
        'postmark_location',
        'postmark_text',
        'width',
        'height',
        'perforation',
        'print_run',
        'for_sale',
        'selling_price',
        'purchase_date',
        'purchasing_price',
        'current_value',
        'location_id',
        'location_detail',
        'personal_remarks',
        'condition',
        'sold_at',
        'sold_price',
    ];

    protected $casts = [
        'for_sale' => 'boolean',
        'mnh' => 'boolean',
        'hinged' => 'boolean',
        'postmarked' => 'boolean',
        'special_postmark' => 'boolean',
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

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function nominalValue()
    {
        return $this->belongsTo(NominalValue::class);
    }

    public function stampType()
    {
        return $this->belongsTo(StampType::class, 'type_id');
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
            $this->nominalValue?->name,
            $this->country?->name,
            $this->year,
        ]);

        return implode(' · ', $parts) ?: 'Stamp #'.$this->id;
    }
}
