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

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function banknotes(): HasMany
    {
        return $this->hasMany(Banknote::class);
    }

    public function coins(): HasMany
    {
        return $this->hasMany(Coin::class);
    }

    public function postcards(): HasMany
    {
        return $this->hasMany(Postcard::class);
    }

    public function stamps(): HasMany
    {
        return $this->hasMany(Stamp::class);
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
