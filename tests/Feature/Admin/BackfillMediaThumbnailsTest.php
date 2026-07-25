<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\MediaFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackfillMediaThumbnailsTest extends TestCase
{
    use RefreshDatabase;

    private function makeImageWithoutThumb(int $width = 800, int $height = 600): MediaFile
    {
        $book = Book::create(['title' => 'Test Book']);
        $path = "books/{$book->id}/original.webp";

        $fake = UploadedFile::fake()->image('original.jpg', $width, $height);
        $image = \Intervention\Image\Laravel\Facades\Image::decode($fake->getRealPath())
            ->encode(new \Intervention\Image\Encoders\WebpEncoder())
            ->toString();

        Storage::disk('b2')->put($path, $image);

        return MediaFile::create([
            'attachable_type' => Book::class,
            'attachable_id' => $book->id,
            'disk' => 'b2',
            'path' => $path,
            'thumb_path' => null,
            'mime_type' => 'image/webp',
            'size' => strlen($image),
            'collection' => 'images',
            'is_main' => true,
            'sort_order' => 0,
        ]);
    }

    public function test_dry_run_reports_count_without_making_changes(): void
    {
        $media = $this->makeImageWithoutThumb();

        $this->artisan('media:backfill-thumbnails --dry-run')
            ->expectsOutputToContain('1 images have no thumbnail yet')
            ->assertExitCode(0);

        $this->assertNull($media->fresh()->thumb_path);
    }

    public function test_generates_thumbnail_for_large_image(): void
    {
        $media = $this->makeImageWithoutThumb(800, 600);

        $this->artisan('media:backfill-thumbnails')->assertExitCode(0);

        $media->refresh();
        $this->assertNotNull($media->thumb_path);
        $this->assertNotSame($media->path, $media->thumb_path);
        Storage::disk('b2')->assertExists($media->thumb_path);
    }

    public function test_marks_already_small_image_as_handled_without_new_file(): void
    {
        $media = $this->makeImageWithoutThumb(200, 150);

        $this->artisan('media:backfill-thumbnails')->assertExitCode(0);

        $media->refresh();
        $this->assertSame($media->path, $media->thumb_path);
    }

    public function test_reports_nothing_to_do_when_all_images_have_thumbs(): void
    {
        $this->artisan('media:backfill-thumbnails')
            ->expectsOutputToContain('Nothing to backfill')
            ->assertExitCode(0);
    }

    public function test_limit_option_only_processes_that_many(): void
    {
        $this->makeImageWithoutThumb();
        $this->makeImageWithoutThumb();
        $this->makeImageWithoutThumb();

        $this->artisan('media:backfill-thumbnails --limit=2')->assertExitCode(0);

        $this->assertSame(2, MediaFile::whereNotNull('thumb_path')->count());
        $this->assertSame(1, MediaFile::whereNull('thumb_path')->count());
    }
}
