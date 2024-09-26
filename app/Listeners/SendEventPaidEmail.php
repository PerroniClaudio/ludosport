<?php

namespace App\Listeners;

use App\Events\EventPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendEventPaidEmail {
    /**
     * Create the event listener.
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventPaid $event): void {
        //

        $order = $event->order;
        $event = $event->event;

        Mail::to($order->user->email)->send(new \App\Mail\EventPaid($order, $event));
    }
}
