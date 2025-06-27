<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class UserCacheService {
    // Durata cache in minuti (da configurazione)
    private function getMetadataTtl(): int {
        return config('user_cache.ttl.metadata', 1440);
    }

    private function getQueryTtl(): int {
        return config('user_cache.ttl.query', 60);
    }

    private function getPageTtl(): int {
        return config('user_cache.ttl.page', 15);
    }

    /**
     * Genera la chiave cache per i ruoli
     */
    public function getRolesCacheKey(): string {
        return 'roles.all';
    }

    /**
     * Genera la chiave cache per una query utente specifica
     */
    public function getUserQueryCacheKey(string $authUserRole, int $authUserId, string $selectedRole, int $page): string {
        return "users.query.{$authUserRole}.{$authUserId}.{$selectedRole}.{$page}";
    }

    /**
     * Genera la chiave cache per i metadati utente (accademie/scuole)
     */
    public function getUserMetadataCacheKey(int $userId, string $type): string {
        return "user.metadata.{$userId}.{$type}";
    }

    /**
     * Cache dei ruoli
     */
    public function getCachedRoles() {
        return Cache::remember(
            $this->getRolesCacheKey(),
            $this->getMetadataTtl(),
            fn() => Role::all()
        );
    }

    /**
     * Cache dell'accademia primaria dell'utente
     */
    public function getCachedUserPrimaryAcademy(User $user) {
        return Cache::remember(
            $this->getUserMetadataCacheKey($user->id, 'primary_academy'),
            $this->getMetadataTtl(),
            fn() => $user->primaryAcademy()
        );
    }

    /**
     * Cache della scuola primaria dell'utente
     */
    public function getCachedUserPrimarySchool(User $user) {
        return Cache::remember(
            $this->getUserMetadataCacheKey($user->id, 'primary_school'),
            $this->getMetadataTtl(),
            fn() => $user->primarySchool()
        );
    }

    /**
     * Cache delle accademie dell'istruttore
     */
    public function getCachedInstructorAcademies(User $user) {
        return Cache::remember(
            $this->getUserMetadataCacheKey($user->id, 'instructor_academies'),
            $this->getMetadataTtl(),
            fn() => $user->academies()->pluck('academy_id')->toArray()
        );
    }

    /**
     * Cache della query utenti con paginazione
     */
    public function getCachedUserQuery(callable $queryBuilder, string $authUserRole, int $authUserId, string $selectedRole, int $page) {
        $cacheKey = $this->getUserQueryCacheKey($authUserRole, $authUserId, $selectedRole, $page);

        return Cache::remember(
            $cacheKey,
            $this->getQueryTtl(),
            $queryBuilder
        );
    }

    /**
     * Invalida la cache per un utente specifico
     */
    public function invalidateUserCache(int $userId): void {
        // Invalida i metadati dell'utente
        $metadataTypes = ['primary_academy', 'primary_school', 'instructor_academies'];
        foreach ($metadataTypes as $type) {
            Cache::forget($this->getUserMetadataCacheKey($userId, $type));
        }

        // Invalida le query cache che potrebbero includere questo utente
        $this->invalidateQueryCaches();
    }

    /**
     * Invalida tutte le cache delle query (da usare quando ci sono cambiamenti globali)
     */
    public function invalidateQueryCaches(): void {
        // Pattern per invalidare tutte le cache delle query utenti
        $pattern = 'users.query.*';

        // Su Redis possiamo usare pattern matching
        if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
            $redis = \Illuminate\Support\Facades\Redis::connection('cache');
            $keys = $redis->keys(config('cache.prefix') . $pattern);
            if (!empty($keys)) {
                $redis->del($keys);
            }
        } else {
            // Fallback: invalidiamo con un timestamp globale
            Cache::forget('users.query.invalidation_timestamp');
            Cache::put('users.query.invalidation_timestamp', now(), $this->getQueryTtl());
        }
    }

    /**
     * Invalida la cache dei ruoli
     */
    public function invalidateRolesCache(): void {
        Cache::forget($this->getRolesCacheKey());
    }

    /**
     * Ottiene il timestamp di invalidazione per le query cache
     */
    public function getQueryInvalidationTimestamp() {
        return Cache::get('users.query.invalidation_timestamp');
    }

    /**
     * Verifica se la cache è ancora valida basandosi sul timestamp di invalidazione
     */
    public function isQueryCacheValid(string $cacheKey): bool {
        $invalidationTimestamp = $this->getQueryInvalidationTimestamp();

        if (!$invalidationTimestamp) {
            return true; // Nessuna invalidazione globale
        }

        $cacheTimestamp = Cache::get($cacheKey . '.timestamp');

        return $cacheTimestamp && $cacheTimestamp > $invalidationTimestamp;
    }
}
