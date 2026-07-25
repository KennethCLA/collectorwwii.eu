<?php

// app/Http/Controllers/Admin/MediaFileController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banknote;
use App\Models\Book;
use App\Models\Coin;
use App\Models\Item;
use App\Models\Magazine;
use App\Models\MapLocation;
use App\Models\MediaFile;
use App\Models\Newspaper;
use App\Models\Postcard;
use App\Models\Stamp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class MediaFileController extends Controller
{
    /**
     * Centrale definitie van wat mag per type.
     */
    private const TYPES = [
        'books' => [
            'model' => Book::class,
            'disk' => 'b2',
            'folder' => 'books',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200, // KB
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200, // KB
                ],
            ],
        ],
        'items' => [
            'model' => Item::class,
            'disk' => 'b2',
            'folder' => 'items',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200, // KB
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200, // KB
                ],
            ],
        ],
        'banknotes' => [
            'model' => Banknote::class,
            'disk' => 'b2',
            'folder' => 'banknotes',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200,
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200,
                ],
            ],
        ],
        'coins' => [
            'model' => Coin::class,
            'disk' => 'b2',
            'folder' => 'coins',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200,
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200,
                ],
            ],
        ],
        'magazines' => [
            'model' => Magazine::class,
            'disk' => 'b2',
            'folder' => 'magazines',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200,
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200,
                ],
            ],
        ],
        'newspapers' => [
            'model' => Newspaper::class,
            'disk' => 'b2',
            'folder' => 'newspapers',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200,
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200,
                ],
            ],
        ],
        'postcards' => [
            'model' => Postcard::class,
            'disk' => 'b2',
            'folder' => 'postcards',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200,
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200,
                ],
            ],
        ],
        'stamps' => [
            'model' => Stamp::class,
            'disk' => 'b2',
            'folder' => 'stamps',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200,
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200,
                ],
            ],
        ],
        'map-locations' => [
            'model' => MapLocation::class,
            'disk' => 'b2',
            'folder' => 'map-locations',
            'collections' => [
                'images' => [
                    'mimetypes' => 'image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif',
                    'max' => 51200,
                ],
                'files' => [
                    'mimetypes' => 'application/pdf',
                    'max' => 51200,
                ],
            ],
        ],
    ];

    private function typeConfigOrFail(string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type];
    }

    private function resolveAttachableOrFail(string $type, int|string $id)
    {
        $cfg = $this->typeConfigOrFail($type);
        $modelClass = $cfg['model'];

        return $modelClass::query()->findOrFail($id);
    }

    public function store(Request $request, string $type, int $id)
    {
        $cfg = $this->typeConfigOrFail($type);
        $attachable = $this->resolveAttachableOrFail($type, $id);

        $this->authorize('update', $attachable);

        $allowedCollections = array_keys($cfg['collections']);

        $validated = $request->validate([
            'collection' => ['required', 'string', Rule::in($allowedCollections)],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file'], // mimetype/max voegen we hieronder toe per collection
        ]);

        $collection = $validated['collection'];
        $rulesCfg = $cfg['collections'][$collection];

        // Collection-specifieke constraints
        $request->validate([
            'files.*' => [
                'file',
                'max:'.$rulesCfg['max'],
                'mimetypes:'.$rulesCfg['mimetypes'],
            ],
        ]);

        $disk = $cfg['disk'];

        // Main image logic + sort order (alleen voor images)
        $hasMainImage = $collection === 'images'
            ? $attachable->images()->where('is_main', true)->exists()
            : true;

        $nextSort = null;
        if ($collection === 'images') {
            $currentMax = (int) ($attachable->images()->max('sort_order') ?? 0);
            $nextSort = $attachable->images()->exists() ? $currentMax + 1 : 0;
        }

        $uploadedPaths = []; // best-effort cleanup bij fouten

        foreach ($request->file('files', []) as $i => $uploaded) {
            $folder = "{$cfg['folder']}/{$attachable->id}";
            $makeMain = ($collection === 'images') && (! $hasMainImage && $i === 0);

            $thumbPath = null;

            // Convert images to WebP for smaller file size and faster loads
            if ($collection === 'images' && in_array($uploaded->getMimeType(), [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'image/heic', 'image/heif',
            ])) {
                $filename = (string) Str::uuid().'.webp';
                $img = Image::decode($uploaded->getRealPath());
                $webpData = $img->encode(new WebpEncoder(quality: 85))->toString();
                $path = "{$folder}/{$filename}";
                Storage::disk($disk)->put($path, $webpData);
                $mimeType = 'image/webp';
                $size = strlen($webpData);

                if ($img->width() > 400) {
                    $thumbFilename = (string) Str::uuid().'-thumb.webp';
                    $thumbData = $img->scaleDown(width: 400)->encode(new WebpEncoder(quality: 75))->toString();
                    $thumbPath = "{$folder}/{$thumbFilename}";
                    Storage::disk($disk)->put($thumbPath, $thumbData);
                }
            } else {
                $ext = strtolower($uploaded->extension() ?: 'bin');
                $filename = (string) Str::uuid().'.'.$ext;
                $path = $uploaded->storeAs($folder, $filename, $disk);
                $mimeType = $uploaded->getMimeType();
                $size = $uploaded->getSize();
            }

            $uploadedPaths[] = [$disk, $path];
            if ($thumbPath) {
                $uploadedPaths[] = [$disk, $thumbPath];
            }

            try {
                $attachable->media()->create([
                    'disk'          => $disk,
                    'path'          => $path,
                    'thumb_path'    => $thumbPath,
                    'mime_type'     => $mimeType,
                    'size'          => $size,
                    'original_name' => $uploaded->getClientOriginalName(),
                    'collection'    => $collection,
                    'is_main'       => $makeMain,
                    'sort_order'    => $collection === 'images' ? $nextSort++ : null,
                ]);
            } catch (\Throwable $e) {
                try {
                    Storage::disk($disk)->delete(array_filter([$path, $thumbPath]));
                } catch (\Throwable $deleteErr) {
                    Log::warning('Media upload cleanup failed', [
                        'disk' => $disk,
                        'path' => $path,
                        'thumb_path' => $thumbPath,
                        'error' => $deleteErr->getMessage(),
                    ]);
                }

                throw $e;
            }

            if ($makeMain) {
                $hasMainImage = true;
            }
        }

        return back()->with('success', 'Upload successful.');
    }

    public function destroy(Request $request, string $type, MediaFile $file)
    {
        $cfg = $this->typeConfigOrFail($type);

        // Guardrails: type match + collection bestaat voor dat type
        abort_unless($file->attachable_type === $cfg['model'], 404);
        abort_unless(isset($cfg['collections'][$file->collection]), 404);

        $attachable = $file->attachable;
        abort_if(! $attachable, 404);

        $this->authorize('update', $attachable);

        $wasMain = (bool) $file->is_main;
        $collection = $file->collection;

        $filePath = $file->path;
        $thumbPath = $file->thumb_path;
        $fileDisk = $file->disk;

        DB::transaction(function () use ($file, $cfg, $attachable, $wasMain, $collection) {
            $file->delete();

            // Als main image weg is: nieuwe main zetten (alleen images)
            if ($wasMain && $collection === 'images') {
                // alles resetten
                MediaFile::where('attachable_type', $cfg['model'])
                    ->where('attachable_id', $attachable->id)
                    ->where('collection', 'images')
                    ->update(['is_main' => 0]);

                // nieuwe main kiezen met duidelijke ordering
                $newMain = $attachable->images()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                if ($newMain) {
                    $newMain->update(['is_main' => 1]);
                }
            }
        });

        // Delete from storage after successful DB transaction (best effort)
        if ($filePath) {
            $deleted = Storage::disk($fileDisk)->delete(array_filter([$filePath, $thumbPath]));

            Log::info('Media delete attempt', [
                'disk' => $fileDisk,
                'path' => $filePath,
                'thumb_path' => $thumbPath,
                'deleted_return' => $deleted,
                'media_id' => $file->id,
                'attachable_type' => $file->attachable_type,
                'attachable_id' => $file->attachable_id,
            ]);
        }

        return back()->with('success', 'File deleted.');
    }

    public function reorder(Request $request, string $type, int $id): \Illuminate\Http\JsonResponse
    {
        $cfg = $this->typeConfigOrFail($type);
        $attachable = $this->resolveAttachableOrFail($type, $id);

        $this->authorize('update', $attachable);

        $validated = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'min:1'],
        ]);

        $ids = array_values(array_unique($validated['ids']));

        // Verify every supplied ID belongs to this attachable's image collection
        $count = MediaFile::where('attachable_type', $cfg['model'])
            ->where('attachable_id', $attachable->id)
            ->where('collection', 'images')
            ->whereIn('id', $ids)
            ->count();

        abort_if($count !== count($ids), 422);

        DB::transaction(function () use ($ids) {
            foreach ($ids as $sortOrder => $mediaId) {
                MediaFile::where('id', $mediaId)->update(['sort_order' => $sortOrder]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function makeMain(Request $request, string $type, MediaFile $file)
    {
        $cfg = $this->typeConfigOrFail($type);

        abort_unless($file->attachable_type === $cfg['model'], 404);
        abort_unless($file->collection === 'images', 404); // makeMain enkel voor images

        $attachable = $file->attachable;
        abort_if(! $attachable, 404);

        $this->authorize('update', $attachable);

        DB::transaction(function () use ($cfg, $attachable, $file) {
            MediaFile::where('attachable_type', $cfg['model'])
                ->where('attachable_id', $attachable->id)
                ->where('collection', 'images')
                ->update(['is_main' => 0]);

            $file->update(['is_main' => 1]);
        });

        return back()->with('success', 'Main image updated.');
    }
}
