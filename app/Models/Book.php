<?php

// app/Models/Book.php

namespace App\Models;

use App\Models\Concerns\HasMainImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, HasMainImage, SoftDeletes;

    protected $fillable = [
        'isbn',
        'title',
        'subtitle',
        'title_first_edition',
        'subtitle_first_edition',
        'description',
        'translator',
        'copyright_year',
        'issue_number',
        'issue_year',
        'series_id',
        'series_number',
        'pages',
        'cover_id',
        'topic_id',
        'copyright_year_first_issue',
        'publisher_name',
        'publisher_first_issue',
        'purchase_price',
        'purchase_date',
        'origin_id',
        'notes',
        'location_id',
        'for_sale',
        'selling_price',
        'weight',
        'width',
        'height',
        'thickness',
        'condition',
        'sold_at',
        'sold_price',
    ];

    protected $casts = [
        'for_sale' => 'boolean',
        'purchase_date' => 'date',
        'copyright_year' => 'integer',
        'issue_year' => 'integer',
        'location_id' => 'integer',
        'selling_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sold_at' => 'date',
        'sold_price' => 'decimal:2',
    ];

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public function origin()
    {
        return $this->belongsTo(Origin::class, 'origin_id');
    }

    public function series()
    {
        return $this->belongsTo(BookSeries::class, 'series_id');
    }

    public function cover()
    {
        return $this->belongsTo(BookCover::class, 'cover_id');
    }

    public function topic()
    {
        return $this->belongsTo(BookTopic::class, 'topic_id');
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'book_authors');
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
