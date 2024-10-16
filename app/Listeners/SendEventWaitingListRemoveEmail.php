<?php

namespace App\Listeners;

use App\Events\EventWaitingListRemove;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendEventWaitingListRemoveEmail implements ShouldQueue
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
    public function handle(EventWaitingListRemove $event): void
    {
        //
        $user = $event->user;
        $event = $event->event;

        Mail::to($user->email)->send(new \App\Mail\EventWaitingListRemoveMail($user, $event));
    }
}
