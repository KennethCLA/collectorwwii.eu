<?php

namespace App\Models\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait HasFlatTree
{
    public static function flatTree(?int $parentId = null, int $depth = 0): Collection
    {
        if ($parentId === null && $depth === 0) {
            return Cache::remember(
                static::class.'::flatTree',
                now()->addHours(6),
                fn () => static::buildFlatTree()
            );
        }

        return static::buildFlatTree($parentId, $depth);
    }

    protected static function buildFlatTree(?int $parentId = null, int $depth = 0): Collection
    {
        return static::where('parent_id', $parentId)->orderBy('name')->get()
            ->flatMap(fn ($node) => collect([(object) ['id' => $node->id, 'name' => str_repeat('— ', $depth).$node->name]])
                ->concat(static::buildFlatTree($node->id, $depth + 1)));
    }

    protected static function bootHasFlatTree(): void
    {
        static::saved(fn () => Cache::forget(static::class.'::flatTree'));
        static::deleted(fn () => Cache::forget(static::class.'::flatTree'));
    }
}
