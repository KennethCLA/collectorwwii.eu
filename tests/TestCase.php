<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Media uploads/deletes always target the 'b2' disk, even when no
        // files are involved (e.g. destroy() clears a directory unconditionally).
        // Fake it globally so tests never need real B2/S3 credentials.
        Storage::fake('b2');
    }
}
