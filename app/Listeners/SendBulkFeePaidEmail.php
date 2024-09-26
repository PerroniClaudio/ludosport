<?php

namespace App\Listeners;

use App\Events\BulkFeePaid;
use App\Mail\FeesBulkPaidMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendBulkFeePaidEmail {
    /**
     * Create the event listener.
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BulkFeePaid $event): void {
        //

        $order = $event->order;

        Mail::to($order->user->email)
            ->send(new FeesBulkPaidMail($order));
    }
}
