<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AggregateLogArchivesCommand extends Command
{
    protected $signature = 'logs:aggregate-monthly 
                           {month? : Month to aggregate (Y-m format, defaults to last month)}
                           {--channel= : Specific channel to aggregate}
                           {--dry-run : Show what would be done without executing}';

    protected $description = 'Aggregate daily log archives into monthly files for better searchability and long-term analysis';

    public function handle(): int
    {
        $month = $this->argument('month') 
            ? Carbon::createFromFormat('Y-m', $this->argument('month'))
            : Carbon::now()->subMonth();
            
        $channel = $this->option('channel');
        $dryRun = $this->option('dry-run');
        
        $disk = Storage::disk(config('logging.archive.disk', 'gcs'));
        $prefix = config('logging.archive.prefix', 'logs');
        
        $this->info("Aggregating log archives for month: {$month->format('Y-m')}");
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made");
        }
        
        // Pattern: logs/YYYY/MM/DD/HH-MM-SS/laravel/*.log (include timestamps)
        $searchPath = "{$prefix}/{$month->format('Y/m')}";
        
        try {
            $files = collect($disk->allFiles($searchPath))
                ->filter(fn($file) => str_contains($file, '/laravel/') && str_ends_with($file, '.log'))
                ->filter(fn($file) => !str_contains($file, '/monthly/')) // Exclude existing monthly archives
                ->filter(function($file) {
                    // Only include files with correct timestamp structure: logs/YYYY/MM/DD/HH-MM-SS/laravel/*.log
                    $pathParts = explode('/', $file);
                    $timestampPart = $pathParts[count($pathParts) - 3] ?? '';
                    return preg_match('/^\d{2}-\d{2}-\d{2}$/', $timestampPart); // HH-MM-SS format
                })
                ->groupBy(function ($file) {
                    // Group by channel: extract filename from path
                    return basename($file);
                });
                
            if ($channel) {
                $files = $files->filter(fn($group, $filename) => 
                    str_contains($filename, $channel));
            }
            
            foreach ($files as $filename => $channelFiles) {
                $this->aggregateChannelFiles($disk, $channelFiles, $month, $filename, $dryRun);
            }
            
            $this->info("✅ Monthly aggregation completed successfully");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Monthly aggregation failed: {$e->getMessage()}");
            return 1;
        }
    }
    
    protected function aggregateChannelFiles($disk, $files, Carbon $month, string $filename, bool $dryRun): void
    {
        if ($files->count() <= 1) {
            $this->line("⏭️  Skipping {$filename}: only " . $files->count() . " file(s)");
            return;
        }
        
        $prefix = config('logging.archive.prefix', 'logs');
        $aggregatedPath = "{$prefix}/{$month->format('Y/m')}/laravel/monthly/{$filename}";
        
        $this->line("📦 Aggregating {$files->count()} daily files into: {$aggregatedPath}");
        
        if ($dryRun) {
            foreach ($files->sort() as $file) {
                $this->line("  - Would merge: {$file}");
            }
            return;
        }
        
        // Download and combine all daily files
        $combinedContent = $files
            ->sort()
            ->map(function($file) use ($disk) {
                $content = $disk->get($file);
                // Extract date and time from path: logs/2026/04/15/14-30-25/laravel/user.log
                $pathParts = explode('/', $file);
                $day = $pathParts[count($pathParts) - 4]; // Extract day
                $time = $pathParts[count($pathParts) - 3]; // Extract HH-MM-SS timestamp
                // Add day and time separator for better readability
                return "=== Day {$day} at {$time} ===\n{$content}";
            })
            ->filter()
            ->implode("\n\n");
            
        // Upload aggregated monthly file
        if ($disk->put($aggregatedPath, $combinedContent)) {
            $this->info("✅ Created monthly archive: {$aggregatedPath}");
            
            // Only remove daily files if explicitly configured (DANGEROUS!)
            $removeDailyFiles = config('logging.archive.remove_daily_after_monthly', false);
            if ($removeDailyFiles) {
                $this->warn("⚠️  Removing daily files (remove_daily_after_monthly=true)");
                foreach ($files as $file) {
                    $disk->delete($file);
                    $this->line("🗑️  Removed daily file: {$file}");
                }
            } else {
                $this->info("ℹ️  Keeping daily files (remove_daily_after_monthly=false)");
                $this->line("   Daily files preserved at: {$prefix}/{$month->format('Y/m')}/*/laravel/{$filename}");
            }
        } else {
            $this->error("❌ Failed to create monthly archive: {$aggregatedPath}");
        }
    }
}