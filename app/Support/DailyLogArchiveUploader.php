<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DailyLogArchiveUploader
{
    public function archive(?CarbonInterface $date = null): ?string
    {
        $logDate = $date
            ? CarbonImmutable::instance($date)->startOfDay()
            : now()->subDay()->startOfDay()->toImmutable();

        $localPath = $this->localPathForDate($logDate);

        if (! is_file($localPath)) {
            return null;
        }

        if (filesize($localPath) === 0) {
            return null;
        }

        $remotePath = $this->remotePathForDate($localPath, $logDate);
        $disk = Storage::disk($this->disk());
        $stream = fopen($localPath, 'r');

        if ($stream === false) {
            throw new RuntimeException("Unable to open log file [{$localPath}] for upload.");
        }

        try {
            $uploaded = $disk->put($remotePath, $stream);
        } finally {
            fclose($stream);
        }

        if (! $uploaded) {
            throw new RuntimeException("Unable to upload log file [{$localPath}] to [{$remotePath}].");
        }

        if (file_put_contents($localPath, '') === false) {
            throw new RuntimeException("Uploaded log file [{$localPath}] but failed to truncate it.");
        }

        return $remotePath;
    }

    public function localPathForDate(CarbonInterface $date): string
    {
        $dailyPath = config('logging.channels.daily.path', storage_path('logs/laravel.log'));
        $directory = dirname($dailyPath);
        $filename = pathinfo($dailyPath, PATHINFO_FILENAME);
        $extension = pathinfo($dailyPath, PATHINFO_EXTENSION);
        $datedFilename = $filename.'-'.$date->format('Y-m-d');

        if ($extension !== '') {
            $datedFilename .= '.'.$extension;
        }

        return $directory.DIRECTORY_SEPARATOR.$datedFilename;
    }

    public function remotePathForDate(string $localPath, CarbonInterface $date): string
    {
        $prefix = trim((string) config('logging.archive.prefix', 'logs'), '/');
        $segments = array_filter([
            $prefix,
            $date->format('Y-m-d'),
            basename($localPath),
        ]);

        return implode('/', $segments);
    }

    public function disk(): string
    {
        return (string) config('logging.archive.disk', 'gcs');
    }
}
