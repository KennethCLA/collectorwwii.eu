<?php

namespace App\Models\Concerns;

use App\Models\MediaFile;

trait HasMainImage
{
    /**
     * Fallback: als er geen is_main is, pak dan eerste image volgens sortering.
     */
    public function mainImageFile(): ?MediaFile
    {
        // Gebruik loaded relations als ze al geladen zijn (show pagina)
        if ($this->relationLoaded('mainImage') || $this->relationLoaded('images')) {
            return $this->getRelation('mainImage')
                ?? ($this->getRelation('images')?->first());
        }

        // Fallback wanneer niet eager loaded (bvb. ergens anders)
        return $this->mainImage()->first() ?? $this->images()->first();
    }

    public function getImageUrlAttribute(): string
    {
        return $this->mainImageFile()?->url()
            ?? asset('images/error-image-not-found.png');
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->mainImageFile()?->thumbUrl()
            ?? asset('images/error-image-not-found.png');
    }
}
