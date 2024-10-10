<?php

namespace App\Jobs;

use App\Events\ParticipantsUpdated;
use App\Models\Event;
use App\Models\EventWaitingList;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckWaitingListJob implements ShouldQueue
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
     * Questo job deve controllare per tutte le waiting list se ci sono persone in fase di pagamento e con il tempo scaduto
     * Se l'evento è nel passato, elimina tutti i record della waiting list di quell'evento'.
     * Se trova persone da rimuovere, deve rimuoverle dalla waiting list e mandare una mail di notifica.
     * Poi dispaccia l'evento che controlla se c'è spazio e prende un altro dalla waiting list.
     */
    public function handle(): void
    {
        $eventsToTrigger = [];
        
        // Prende gli eventi passati che hanno ancora la waiting list e la svuota
        $differentOldEvents = Event::whereIn('id', EventWaitingList::select('event_id')->distinct()->get())
            ->where('start_date', '<', Carbon::now())->get();
        forEach($differentOldEvents as $event) {
            EventWaitingList::where('event_id', $event->id)->delete();
        }

        // Prende in waiting list chi è in fase di pagamento e con il tempo scaduto
        $waitingListExpired = EventWaitingList::where('is_waiting_payment', true)
            ->where('payment_deadline', '<', Carbon::now())
            ->get();

        // Rimuove dalla waiting list e manda mail di notifica
        foreach ($waitingListExpired as $waitingListRecord) {
            $waitingListRecord->delete();
            
            // Invia email di notifica

            // Aggiunge l'evento alla lista di quelli per i quali si deve controllare se prendere altri dalla waiting list
            if (!in_array($event->id, $eventsToTrigger)) {
                $eventsToTrigger[] = $event->id;
            }
        }

        // Dispaccia l'evento che gestisce il pescaggio dalla waiting list
        foreach ($eventsToTrigger as $eventId) {
            // Dispatch the event
            event(new ParticipantsUpdated($eventId));
        }
    }
}
