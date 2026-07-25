<?php

namespace App\Console\Commands;

use App\Models\MediaFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;

class BackfillMediaThumbnails extends Command
{
    protected $signature = 'media:backfill-thumbnails {--dry-run : Only report how many images would be processed} {--limit= : Only process this many images (for testing)}';

    protected $description = 'Generate 400px WebP thumbnails for existing images uploaded before the thumbnail pipeline existed.';

    public function handle(): int
    {
        $query = MediaFile::where('collection', 'images')->whereNull('thumb_path');
        $total = $query->count();

        if ($total === 0) {
            $this->info('Nothing to backfill — every image already has a thumbnail.');

            return 0;
        }

        if ($this->option('dry-run')) {
            $this->info("{$total} images have no thumbnail yet. Run without --dry-run to generate them.");

            return 0;
        }

        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        if ($limit) {
            $total = min($total, $limit);
        }

        $this->info("Backfilling thumbnails for {$total} images...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $done = 0;
        $failed = 0;
        $processOne = function (MediaFile $media) use (&$done, &$failed, $bar) {
            try {
                $this->backfillOne($media);
                $done++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Thumbnail backfill failed', [
                    'media_id' => $media->id,
                    'path' => $media->path,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        };

        if ($limit) {
            $query->orderBy('id')->limit($limit)->get()->each($processOne);
        } else {
            $query->orderBy('id')->chunkById(50, fn ($rows) => $rows->each($processOne));
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. {$done} thumbnails generated, {$failed} failed (see laravel.log).");

        return 0;
    }

    private function backfillOne(MediaFile $media): void
    {
        $disk = Storage::disk($media->disk);

        if (! $disk->exists($media->path)) {
            throw new \RuntimeException('Source file missing on disk.');
        }

        $original = $disk->get($media->path);
        $img = Image::decode($original);

        if ($img->width() <= 400) {
            // Already small enough — nothing to thumbnail, but mark as
            // handled so it's not retried every run.
            $media->update(['thumb_path' => $media->path]);

            return;
        }

        $thumbData = $img->scaleDown(width: 400)->encode(new WebpEncoder(quality: 75))->toString();
        $folder = pathinfo($media->path, PATHINFO_DIRNAME);
        $thumbPath = $folder.'/'.(string) Str::uuid().'-thumb.webp';

        $disk->put($thumbPath, $thumbData);
        $media->update(['thumb_path' => $thumbPath]);
    }
}
