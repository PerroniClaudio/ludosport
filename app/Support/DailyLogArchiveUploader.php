<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Monolog\Handler\StreamHandler;
use RuntimeException;

class DailyLogArchiveUploader
{
    /**
     * Archive all log channels to Google Cloud Storage.
     *
     * @param  CarbonInterface|null  $date  Optional execution timestamp for remote path
     * @param  bool  $force  Force archival of active log files (bypasses today check)
     * @return array<string> Array of uploaded remote paths
     */
    public function archive(?CarbonInterface $date = null, bool $force = false): array
    {
        $executionTime = $date
            ? CarbonImmutable::instance($date)
            : now()->toImmutable();

        $channels = $this->getArchivableChannels();
        $uploaded = [];

        foreach ($channels as $channelName => $channelConfig) {
            try {
                $localPath = $this->getChannelLogPath($channelConfig);

                if (! is_file($localPath)) {
                    Log::debug("Log file not found for channel [{$channelName}]: {$localPath}");
                    continue;
                }

                if (filesize($localPath) === 0) {
                    Log::debug("Log file is empty for channel [{$channelName}]: {$localPath}");
                    continue;
                }

                // Skip active files unless --force is used
                if (! $force && $this->isActiveLogFile($localPath)) {
                    Log::debug("Skipping active log file for channel [{$channelName}]: {$localPath} (use --force to override)");
                    continue;
                }

                $remotePath = $this->remotePathForDate($localPath, $executionTime);
                $this->uploadLogFile($localPath, $remotePath);

                $uploaded[] = $remotePath;
                Log::info("Archived log for channel [{$channelName}] to [{$remotePath}]");
            } catch (\Throwable $e) {
                Log::error("Failed to archive log for channel [{$channelName}]: {$e->getMessage()}");
                // Continue with other channels
            }
        }

        return $uploaded;
    }

    /**
     * Get all archivable log channels from configuration.
     *
     * @return array<string, array>
     */
    protected function getArchivableChannels(): array
    {
        $allChannels = config('logging.channels', []);
        $archivable = [];

        foreach ($allChannels as $name => $config) {
            $driver = $config['driver'] ?? null;

            // Support: daily, single, monolog (with StreamHandler)
            if (in_array($driver, ['daily', 'single'])) {
                $archivable[$name] = $config;
                continue;
            }

            // Monolog with StreamHandler
            if ($driver === 'monolog') {
                $handler = $config['handler'] ?? null;
                if ($handler === StreamHandler::class) {
                    $archivable[$name] = $config;
                }
            }
        }

        return $archivable;
    }

    /**
     * Extract the log file path from channel configuration.
     *
     * @param  array  $channelConfig
     * @return string
     */
    protected function getChannelLogPath(array $channelConfig): string
    {
        // Direct path (daily, single)
        if (isset($channelConfig['path'])) {
            return $channelConfig['path'];
        }

        // Monolog StreamHandler path
        if (isset($channelConfig['with']['stream'])) {
            return $channelConfig['with']['stream'];
        }

        throw new RuntimeException('Unable to determine log file path from channel configuration');
    }

    /**
     * Check if a log file is currently active (being written today).
     *
     * @param  string  $localPath
     * @return bool
     */
    protected function isActiveLogFile(string $localPath): bool
    {
        clearstatcache(true, $localPath);

        $fileModificationTime = filemtime($localPath);
        if ($fileModificationTime === false) {
            return false;
        }

        $modifiedDate = CarbonImmutable::createFromTimestamp($fileModificationTime)->startOfDay();
        $today = now()->startOfDay();

        // Skip if file was modified today (likely still being written)
        return $modifiedDate->equalTo($today);
    }

    /**
     * Upload log file to cloud storage and truncate locally.
     *
     * @param  string  $localPath
     * @param  string  $remotePath
     * @return void
     */
    protected function uploadLogFile(string $localPath, string $remotePath): void
    {
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

        // Truncate file after successful upload
        if (file_put_contents($localPath, '') === false) {
            throw new RuntimeException("Uploaded log file [{$localPath}] but failed to truncate it.");
        }
    }

    /**
     * Generate hierarchical remote path with timestamp: logs/YYYY/MM/DD/HH-MM-SS/filename.log
     *
     * @param  string  $localPath
     * @param  CarbonInterface  $executionTime
     * @return string
     */
    public function remotePathForDate(string $localPath, CarbonInterface $executionTime): string
    {
        $prefix = trim((string) config('logging.archive.prefix', 'logs'), '/');
        
        // Clean filename: remove date patterns (laravel-2026-04-13.log → laravel.log)
        $filename = $this->cleanLogFilename(basename($localPath));

        $segments = array_filter([
            $prefix,
            $executionTime->format('Y'),
            $executionTime->format('m'),
            $executionTime->format('d'),
            $executionTime->format('H-i-s'),
            $filename,
        ]);

        return implode('/', $segments);
    }

    /**
     * Clean log filename by removing date suffixes.
     * Example: laravel-2026-04-13.log → laravel.log
     *
     * @param  string  $filename
     * @return string
     */
    protected function cleanLogFilename(string $filename): string
    {
        // Remove patterns like: -2026-04-13, -20260413
        return preg_replace('/-\d{4}-\d{2}-\d{2}(?=\.)/', '', $filename) ?? $filename;
    }

    /**
     * Get the cloud storage disk name.
     *
     * @return string
     */
    public function disk(): string
    {
        return (string) config('logging.archive.disk', 'gcs');
    }
}
