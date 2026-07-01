<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

class CleanTempMinorDocuments extends Command
{
    protected $signature = 'temp:clean-minor-documents {--hours=24 : Number of hours to consider a file as orphan}';

    protected $description = 'Clean orphaned temporary minor documents files older than specified hours';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $disk = Storage::disk('local');
        $basePath = 'temp/minor_documents';

        // Check if directory exists
        if (! $disk->exists($basePath)) {
            $this->info("❌ Directory {$basePath} does not exist.");

            return self::SUCCESS;
        }

        $cutoffTime = now()->subHours($hours)->getTimestamp();
        $deletedCount = 0;
        $totalBytes = 0;

        try {
            $finder = new Finder;
            $files = $finder->files()->in($disk->path($basePath));

            foreach ($files as $file) {
                $fileTime = filemtime($file->getRealPath());

                if ($fileTime < $cutoffTime) {
                    $fileSize = filesize($file->getRealPath());
                    $relativePath = $basePath.'/'.$file->getFilename();

                    if ($disk->delete($relativePath)) {
                        $deletedCount++;
                        $totalBytes += $fileSize;
                        $this->line("  🗑️  Deleted: {$file->getFilename()} ({$fileSize} bytes)");
                    } else {
                        $this->warn("  ⚠️  Failed to delete: {$file->getFilename()}");
                    }
                }
            }

            if ($deletedCount > 0) {
                $this->info("✅ Cleaned {$deletedCount} orphaned file(s) ({$totalBytes} bytes total)");
            } else {
                $this->info("ℹ️  No orphaned files found older than {$hours} hour(s).");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error cleaning temporary files: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
