<?php

namespace App\Models;

use App\Models\Concerns\HasMainImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coin extends Model
{
    use HasMainImage, SoftDeletes;

    protected $fillable = [
        'country_id',
        'currency_id',
        'nominal_value_id',
        'shape_id',
        'material_id',
        'year',
        'occasion_id',
        'head_of_state_id',
        'strike_mark_id',
        'designer_id',
        'front_image_id',
        'front_text_id',
        'reverse_image_id',
        'reverse_text_id',
        'rim_id',
        'rim_text_id',
        'time_period',
        'number_jaeger',
        'date_of_issue',
        'special_features',
        'gold_silver_content',
        'weight',
        'diameter',
        'thickness',
        'run',
        'current_value',
        'for_sale',
        'selling_price',
        'purchase_date',
        'purchasing_price',
        'location_id',
        'location_detail',
        'personal_remarks',
        'condition',
        'sold_at',
        'sold_price',
    ];

    protected $casts = [
        'for_sale' => 'boolean',
        'purchase_date' => 'date',
        'date_of_issue' => 'date',
        'selling_price' => 'decimal:2',
        'purchasing_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'gold_silver_content' => 'decimal:2',
        'weight' => 'decimal:2',
        'diameter' => 'decimal:2',
        'thickness' => 'decimal:2',
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

    public function shape()
    {
        return $this->belongsTo(CoinShape::class, 'shape_id');
    }

    public function material()
    {
        return $this->belongsTo(CoinMaterial::class, 'material_id');
    }

    public function occasion()
    {
        return $this->belongsTo(CoinOccasion::class, 'occasion_id');
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

        return implode(' · ', $parts) ?: 'Coin #'.$this->id;
    }
}
