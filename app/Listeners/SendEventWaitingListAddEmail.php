<?php

namespace App\Listeners;

use App\Events\EventPaid;
use App\Events\EventWaitingListAdd;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendEventWaitingListAddEmail {
    /**
     * Create the event listener.
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventWaitingListAdd $event): void {
        //

        $user = $event->listItem->user;
        $event = $event->listItem->event;

        Mail::to($user->email)->send(new \App\Mail\EventWaitingListAdd($user, $event));
    }
}
