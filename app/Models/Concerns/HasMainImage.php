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
        // Gebruik loaded relations als ze al geladen zijn (show pagina),
        // maar check elke relatie apart — beide kunnen onafhankelijk
        // wel/niet eager-loaded zijn.
        if ($this->relationLoaded('mainImage')) {
            $main = $this->getRelation('mainImage');
            if ($main) {
                return $main;
            }
        }

        if ($this->relationLoaded('images')) {
            return $this->getRelation('images')->first();
        }

        if ($this->relationLoaded('mainImage')) {
            return null;
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
