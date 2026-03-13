<?php

namespace Tests\Unit;

use App\Support\DailyLogArchiveUploader;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DailyLogArchiveUploaderTest extends TestCase
{
    public function test_it_uploads_the_daily_log_to_gcs_and_truncates_it(): void
    {
        Storage::fake('gcs');

        config([
            'logging.archive.disk' => 'gcs',
            'logging.archive.prefix' => 'logs',
            'logging.channels.daily.path' => storage_path('framework/testing/logs/laravel.log'),
        ]);

        $uploader = new DailyLogArchiveUploader();
        $date = CarbonImmutable::parse('2026-03-12');
        $localPath = $uploader->localPathForDate($date);

        if (! is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0777, true);
        }

        file_put_contents($localPath, "first line\nsecond line");

        $remotePath = $uploader->archive($date);

        $this->assertSame('logs/2026-03-12/laravel-2026-03-12.log', $remotePath);
        Storage::disk('gcs')->assertExists($remotePath);
        $this->assertSame("first line\nsecond line", Storage::disk('gcs')->get($remotePath));
        $this->assertSame('', file_get_contents($localPath));
    }

    public function test_it_skips_missing_daily_logs(): void
    {
        Storage::fake('gcs');

        config([
            'logging.archive.disk' => 'gcs',
            'logging.archive.prefix' => 'logs',
            'logging.channels.daily.path' => storage_path('framework/testing/missing/laravel.log'),
        ]);

        $uploader = new DailyLogArchiveUploader();

        $this->assertNull($uploader->archive(CarbonImmutable::parse('2026-03-12')));
        Storage::disk('gcs')->assertDirectoryEmpty('/');
    }
}
