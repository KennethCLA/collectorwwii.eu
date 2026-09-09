<?php

namespace Tests\Feature\Public;

use App\Models\MediaFile;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    public function test_shared_gallery_has_one_slide_per_file_with_main_first(): void
    {
        $image = fn ($id, $path) => (new MediaFile(['disk' => 'b2', 'path' => $path]))->forceFill(['id' => $id]);
        $main = $image(2, 'main.jpg');
        $other = $image(1, 'other.jpg');
        $last = $image(3, 'last.jpg');
        $duplicateFile = $image(4, 'main.jpg');
        $html = view('partials.show-media', [
            'title' => 'Gallery', 'main' => $main, 'mainUrl' => $main->url(),
            'images' => collect([$other, $main, $last, $duplicateFile, $other]), 'pdfs' => collect(),
        ])->render();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $slides = $xpath->query('//a[@data-fancybox]');
        $this->assertCount(3, $slides);
        $this->assertSame([$main->url(), $other->url(), $last->url()], array_map(
            fn ($node) => $node->getAttribute('href'), iterator_to_array($slides)
        ));
        $this->assertCount(3, $xpath->query('//button'));
    }

    public function test_shared_gallery_handles_no_main_single_image_and_no_images(): void
    {
        $image = (new MediaFile(['disk' => 'b2', 'path' => 'only.jpg']))->forceFill(['id' => 1]);
        foreach ([collect([$image]), collect()] as $images) {
            $html = view('partials.show-media', ['title' => 'Gallery', 'main' => null, 'mainUrl' => null, 'images' => $images, 'pdfs' => collect()])->render();
            $this->assertSame($images->count(), substr_count($html, 'data-fancybox="'));
            $this->assertStringNotContainsString('<button', $html);
        }
    }
}
