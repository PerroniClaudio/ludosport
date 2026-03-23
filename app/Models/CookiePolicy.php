<?php

namespace App\Models;

use App\Jobs\InvalidateAllUserSessionsJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CookiePolicy extends Model
{
    protected $fillable = ['content', 'last_modified_by'];

    /**
     * Ottieni o crea il record di cookie policy
     */
    public static function getOrCreate(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            ['content' => '']
        );
    }

    /**
     * Relazione con l'utente che ha fatto l'ultima modifica
     */
    public function lastModifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    /**
     * Aggiorna il contenuto e invalida tutte le approvazioni
     */
    public function updateAndInvalidate(string $content, ?int $userId = null): void
    {
        $this->update([
            'content' => $content,
            'last_modified_by' => $userId,
        ]);

        // Sloggamenti globali: resetta le approvazioni di tutti
        // User::query()->update(['cookie_policy_accepted_at' => null]);

        // Job asincrono per invalidare le sessioni
        InvalidateAllUserSessionsJob::dispatch();
    }

    /**
     * Controlla se è stata modificata dall'ultima approvazione di un utente
     */
    public function isNewerThan(?string $acceptedAt): bool
    {
        if (! $acceptedAt) {
            return true;
        }

        return $this->updated_at->isAfter($acceptedAt);
    }
}
