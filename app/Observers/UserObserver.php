<?php

namespace App\Observers;

use App\Models\User;
use App\Services\UserCacheService;

class UserObserver {
    protected UserCacheService $cacheService;

    public function __construct(UserCacheService $cacheService) {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void {
        $this->invalidateCaches($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void {
        $this->invalidateCaches($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void {
        $this->invalidateCaches($user);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void {
        $this->invalidateCaches($user);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void {
        $this->invalidateCaches($user);
    }

    /**
     * Invalida le cache relative all'utente
     */
    private function invalidateCaches(User $user): void {
        // Invalida la cache specifica dell'utente
        $this->cacheService->invalidateUserCache($user->id);

        // Invalida tutte le query cache per gli elenchi utenti
        $this->cacheService->invalidateQueryCaches();
    }
}
