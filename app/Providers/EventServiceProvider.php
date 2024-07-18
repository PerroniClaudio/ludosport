<?php

namespace App\Providers;

use AcademyObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Jobs\UploadUserToMeiliSearch; // Assicurati di usare il namespace corretto per il tuo job
use App\Models\Academy;
use App\Models\Clan;
use App\Models\School;
use App\Models\User;
use ClanObserver;
use SchoolObserver;
use UserObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Altri eventi e listeners...
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        parent::boot();

        User::observe(UserObserver::class);
        Academy::observe(AcademyObserver::class);
        School::observe(SchoolObserver::class);
        Clan::observe(ClanObserver::class);
    }
}