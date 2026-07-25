<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Handles image/PDF uploads submitted alongside a create form, before the
 * model has its own edit page to upload through. Images are converted to
 * WebP and get a 400px thumbnail generated, matching MediaFileController's
 * upload pipeline (the one used from every type's edit page) so a photo
 * attached at create time isn't treated any differently from one added later.
 */
trait HandlesInlineMediaUploads
{
    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $imageUploads
     * @param  array<int, \Illuminate\Http\UploadedFile>  $pdfUploads
     * @param  array<int, array{0: string, 1: string}>  $uploadedForCleanup
     */
    protected function attachInlineMedia(
        Model $model,
        string $folderBase,
        array $imageUploads,
        array $pdfUploads,
        int $mainIndex,
        array &$uploadedForCleanup,
    ): void {
        $disk = 'b2';

        if (count($imageUploads) > 0) {
            $mainIndex = max(0, min($mainIndex, count($imageUploads) - 1));
            $nextSort = 0;

            foreach ($imageUploads as $i => $uploaded) {
                $filename = (string) Str::uuid().'.webp';
                $img = Image::decode($uploaded->getRealPath());
                $webpData = $img->encode(new WebpEncoder(quality: 85))->toString();
                $path = "{$folderBase}/{$filename}";
                Storage::disk($disk)->put($path, $webpData);
                $uploadedForCleanup[] = [$disk, $path];

                $thumbPath = null;
                if ($img->width() > 400) {
                    $thumbFilename = (string) Str::uuid().'-thumb.webp';
                    $thumbData = $img->scaleDown(width: 400)->encode(new WebpEncoder(quality: 75))->toString();
                    $thumbPath = "{$folderBase}/{$thumbFilename}";
                    Storage::disk($disk)->put($thumbPath, $thumbData);
                    $uploadedForCleanup[] = [$disk, $thumbPath];
                }

                $model->media()->create([
                    'disk' => $disk,
                    'path' => $path,
                    'thumb_path' => $thumbPath,
                    'mime_type' => 'image/webp',
                    'size' => strlen($webpData),
                    'original_name' => $uploaded->getClientOriginalName(),
                    'collection' => 'images',
                    'is_main' => $i === $mainIndex,
                    'sort_order' => $nextSort++,
                ]);
            }

            $this->ensureExactlyOneMainImage($model);
        }

        foreach ($pdfUploads as $uploaded) {
            $filename = (string) Str::uuid().'.'.$uploaded->extension();
            $path = $uploaded->storeAs($folderBase, $filename, $disk);
            $uploadedForCleanup[] = [$disk, $path];

            $model->media()->create([
                'disk' => $disk,
                'path' => $path,
                'mime_type' => $uploaded->getMimeType(),
                'size' => $uploaded->getSize(),
                'original_name' => $uploaded->getClientOriginalName(),
                'collection' => 'files',
                'is_main' => false,
                'sort_order' => null,
            ]);
        }
    }

    private function ensureExactlyOneMainImage(Model $model): void
    {
        $imagesQuery = $model->media()->where('collection', 'images');
        $mainCount = (int) (clone $imagesQuery)->where('is_main', 1)->count();

        if ($mainCount === 0) {
            $first = (clone $imagesQuery)->orderBy('sort_order')->orderBy('id')->first();
            (clone $imagesQuery)->update(['is_main' => 0]);
            $first?->update(['is_main' => 1]);
        } elseif ($mainCount > 1) {
            $keepId = (clone $imagesQuery)->where('is_main', 1)->orderBy('id', 'desc')->value('id');
            (clone $imagesQuery)->where('is_main', 1)->where('id', '!=', $keepId)->update(['is_main' => 0]);
        }
    }
}
