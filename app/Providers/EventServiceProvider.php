<?php

namespace App\Providers;

use App\Events\BulkFeePaid;
use App\Events\EventPaid;
use App\Events\FeePaid;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\ParticipantsUpdated;
use App\Listeners\SendBulkFeePaidEmail;
use App\Listeners\SendEventPaidEmail;
use App\Listeners\UpdateEventParticipants;
use App\Listeners\SendFeePaidEmail;

class EventServiceProvider extends ServiceProvider {
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ParticipantsUpdated::class => [
            UpdateEventParticipants::class,
        ],
        FeePaid::class => [
            SendFeePaidEmail::class,
        ],
        BulkFeePaid::class => [
            SendBulkFeePaidEmail::class,
        ],
        EventPaid::class => [
            SendEventPaidEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot() {
        parent::boot();
    }
}
