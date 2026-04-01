<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class S3FileResponseTest extends TestCase
{
    public function test_get_files_from_s3_disk(): void
    {
        Storage::fake('s3');

        Storage::disk('s3')->put('folder/sample.txt', 'hello-s3');

        $files = Storage::disk('s3')->allFiles('folder');

        $this->assertContains('folder/sample.txt', $files);
        $this->assertSame('hello-s3', Storage::disk('s3')->get('folder/sample.txt'));
    }
}
