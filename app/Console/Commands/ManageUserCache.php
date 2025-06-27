<?php

namespace App\Console\Commands;

use App\Http\Controllers\PaginatedUserController;
use App\Services\UserCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ManageUserCache extends Command {
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:cache 
                            {action : The action to perform (clear|warm|status)}
                            {--role= : Specific role to warm cache for}
                            {--user= : Specific user ID to clear cache for}';

    /**
     * The console command description.
     */
    protected $description = 'Manage user listing cache';

    protected UserCacheService $cacheService;

    public function __construct(UserCacheService $cacheService) {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        $action = $this->argument('action');

        switch ($action) {
            case 'clear':
                $this->clearCache();
                break;
            case 'warm':
                $this->warmCache();
                break;
            case 'status':
                $this->showCacheStatus();
                break;
            default:
                $this->error("Unknown action: {$action}");
                $this->line("Available actions: clear, warm, status");
                return 1;
        }

        return 0;
    }

    /**
     * Clear cache
     */
    private function clearCache() {
        if ($userId = $this->option('user')) {
            $this->cacheService->invalidateUserCache((int) $userId);
            $this->info("Cache cleared for user ID: {$userId}");
        } else {
            $this->cacheService->invalidateQueryCaches();
            $this->cacheService->invalidateRolesCache();
            $this->info("All user caches cleared");
        }
    }

    /**
     * Warm cache by making common requests
     */
    private function warmCache() {
        $this->info("Warming up cache...");

        // Warm roles cache
        $this->cacheService->getCachedRoles();
        $this->line("✓ Roles cache warmed");

        // Common role combinations to pre-cache
        $roles = ['athlete', 'instructor', 'technician', 'rector', 'dean', 'manager', 'admin'];
        $specificRole = $this->option('role');

        if ($specificRole) {
            $roles = [$specificRole];
        }

        $this->withProgressBar($roles, function ($role) {
            // This would require actual requests to warm the cache effectively

            $controller = new PaginatedUserController(
                $this->cacheService
            );
            $controller->warmCache($role);

            // For now, we just indicate the structure
            $this->line(" ✓ Ready to warm {$role} queries");
        });

        $this->newLine();
        $this->info("Cache warming completed");
    }

    /**
     * Show cache status
     */
    private function showCacheStatus() {
        $this->info("Cache Status:");

        // Check roles cache
        $rolesKey = $this->cacheService->getRolesCacheKey();
        $rolesExists = Cache::has($rolesKey);
        $this->line("Roles cache: " . ($rolesExists ? "✓ Cached" : "✗ Not cached"));

        // Check invalidation timestamp
        $invalidationTimestamp = $this->cacheService->getQueryInvalidationTimestamp();
        if ($invalidationTimestamp) {
            $this->line("Last global invalidation: {$invalidationTimestamp}");
        } else {
            $this->line("No global invalidation recorded");
        }

        // Show cache store information
        $store = Cache::getStore();
        $this->line("Cache store: " . get_class($store));

        if (method_exists($store, 'getPrefix')) {
            $this->line("Cache prefix: " . $store->getPrefix());
        }
    }
}
