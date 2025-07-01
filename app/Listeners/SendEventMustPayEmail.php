<?php

namespace App\Listeners;

use App\Events\EventMustPay;
use App\Mail\EventMustPayMail;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendEventMustPayEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventMustPay $event): void
    {
        //
        $listItem = $event->listItem;
        $event = $listItem->event;

        // Assicurati che start_date sia un'istanza di Carbon
        if (!($listItem->payment_deadline instanceof Carbon)) {
            $listItem->payment_deadline = Carbon::parse($listItem->payment_deadline);
        }

        if(!($event->start_date instanceof Carbon)) {
            $event->start_date = Carbon::parse($event->start_date);
        }

        Mail::to($listItem->user->email)->send(new EventMustPayMail($listItem, $event));
    }
}
