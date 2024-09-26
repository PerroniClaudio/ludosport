<?php

namespace App\Listeners;

use App\Events\FeePaid;
use App\Mail\FeePaidMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

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

        $order = $event->order;

        Mail::to($order->user->email)
            ->send(new FeePaidMail($order));
    }
}
