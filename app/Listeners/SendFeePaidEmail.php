<?php

namespace App\Listeners;

use App\Events\FeePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFeePaidEmail {
    /**
     * Create the event listener.
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FeePaid $event): void {
        //

        //$order = $event->order;
    }
}
