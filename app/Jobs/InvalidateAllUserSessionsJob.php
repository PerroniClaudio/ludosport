<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class InvalidateAllUserSessionsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Invalida tutti i token API Sanctum
        DB::table('personal_access_tokens')->update(['last_used_at' => null]);

        // Invalida tutte le sessioni web
        DB::table('sessions')->delete();
    }
}
