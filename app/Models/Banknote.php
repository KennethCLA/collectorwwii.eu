<?php

namespace App\Models;

use App\Models\Concerns\HasMainImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banknote extends Model
{
    use HasMainImage, SoftDeletes;

    protected $fillable = [
        'country_id',
        'currency_id',
        'nominal_value_id',
        'series_id',
        'time_period_id',
        'head_of_state_id',
        'colour_id',
        'designer_id',
        'watermark_id',
        'year',
        'variation',
        'number_on_note',
        'special_features',
        'number_jaeger',
        'date_of_issue',
        'front_image',
        'front_text',
        'reverse_image',
        'reverse_text',
        'width',
        'height',
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

    public function series()
    {
        return $this->belongsTo(BanknoteSeries::class, 'series_id');
    }

    public function timePeriod()
    {
        return $this->belongsTo(BanknoteTimePeriod::class, 'time_period_id');
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
            $this->currency?->name,
            $this->year,
        ]);

        return implode(' · ', $parts) ?: 'Banknote #'.$this->id;
    }
}
