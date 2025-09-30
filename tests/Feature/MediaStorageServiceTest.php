<?php

namespace Tests\Feature;
use App\Http\Services\Public\MediaStorageService;
use Date;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
class MediaStorageServiceTest extends TestCase
{
    public function test_storeSingleImage_with_real_file()
    {
        // مسیر واقعی روی دیسک public
        $destinationDir = public_path('tests/images/').Date('Y/m/d');

        // اگر مسیر وجود ندارد بساز
        if (!File::exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0777, true);
        }

        // فایل واقعی برای آپلود
        $realFile = new UploadedFile(
            public_path('avatar.jpg'), // مسیر واقعی فایل ورودی
            'avatar.jpg',
            'image/jpeg',
            null,
            true // test mode
        );

        // سرویس MediaStorageService
        $service = app(MediaStorageService::class);

        // ذخیره عکس
        $storedPath = $service->storeSingleImage($realFile, 'tests/images', null);

        // بررسی اینکه مسیر برگشتی رشته است
        $this->assertIsString($storedPath);

        // بررسی اینکه فایل واقعاً ذخیره شده
        $this->assertFileExists(public_path($storedPath));

        // پاکسازی بعد از تست (اختیاری)
        if (File::exists(public_path($storedPath))) {
            File::delete(public_path($storedPath));
        }
    }
}