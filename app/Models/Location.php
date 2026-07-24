<?php

// app/Models/Location.php

namespace App\Models;

use App\Models\Concerns\HasFlatTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFlatTree;

    protected $fillable = ['name', 'parent_id'];

    public function books()
    {
        return $this->hasMany(\App\Models\Book::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')->orderBy('name');
    }
}
