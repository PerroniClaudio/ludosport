<?php

namespace App\Listeners;

use App\Events\EventPaid;
use App\Events\ParticipantsUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Event;
use App\Models\EventWaitingList;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UpdateEventParticipants
{

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
    public function handle(ParticipantsUpdated $triggeredEvent): void
    {
        $event = Event::find($triggeredEvent->eventId);

        // Check if the event has free spots
        // max_participants è nullable, se è null non ci sono limiti, quindi non c'è nemmeno la lista d'attesa. Quindi non ci sono controlli aggiuntivi da inserire qui
        $hasFreeSpots = false;
        // per vedere se prendere altri dalla waiting list si devono contare i partecipanti + quelli in waiting list con lo stato deve pagare
        $waitingPayments = EventWaitingList::where('event_id', $event->id)->where('is_waiting_payment', true)->count();
        if($event->resultType() === 'enabling'){
            $hasFreeSpots = ($event->instructorResults()->count() + $waitingPayments) < $event->max_participants;
        } else if($event->resultType() === 'ranking'){
            $hasFreeSpots = ($event->results()->count() + $waitingPayments) < $event->max_participants;
        }
        
        // Check if the event has free spots and if there are users in the waiting list (that are not already in waiting payment)
        while ($hasFreeSpots && (EventWaitingList::where('event_id', $event->id)->where('is_waiting_payment', false)->count() > 0)) {
            // Picks the first element in the waiting list
            $waitingListItem = EventWaitingList::where('event_id', $event->id)->where('is_waiting_payment', false)->first();
            
            if($waitingListItem) {

                // Check if payment is required
                if($event->is_free || $event->price == 0) {

                    
                    if($event->resultType() === 'enabling') {
                        $event->instructorResults()->create([
                            'user_id' => $waitingListItem->user_id,
                            'weapon_form_id' => $event->weapon_form_id,
                        ]);
                    } else if($event->resultType() === 'ranking') {
                        $event->results()->create([
                            'user_id' => $waitingListItem->user_id,
                            'war_points' => 0,
                            'style_points' => 0,
                            'total_points' => 0,
                        ]);
                    }
                    
                    $order = Order::where('user_id', $waitingListItem->user_id)
                        ->whereHas('items', function ($query) use ($event) {
                            $query->where(['product_type' => 'event_participation', 'product_code' => $event->id]);
                        })->first();
                    if($order) {
                        $order->update([
                            'status' => 2, 
                            'payment_method' => 'free',
                        ]);
                    } else {
                        // Se l'ordine non esiste si deve creare e completare (non dovrebbe succedere mai)
                        $user = $waitingListItem->user;
                        $lastInvoice = $user->invoices()->latest()->first();
                        // L'invoice magari gliela facciamo completare solo su richiesta. Altrimenti dovremmo farli passare della pagina d'acquistoanche se è gratuito.
                        $invoice = $user->invoices()->create([
                            'user_id' => $user->id,
                            'name' => $lastInvoice ? ($lastInvoice->name ?: $user->name) : $user->name,
                            'surname' => $lastInvoice ? ($lastInvoice->surname ?: ($user->surname ?: '')) : ($user->surname ?: ''),
                            'address' => $lastInvoice ? ($lastInvoice->address ?: json_encode([
                                'address' => '',
                                'zip' => '',
                                'city' => '',
                                'country' => 'Italy',
                            ])) : json_encode([
                                'address' => '',
                                'zip' => '',
                                'city' => '',
                                'country' => 'Italy',
                            ]),
                            'vat' => $lastInvoice ? ($lastInvoice->vat ?: '') : '',
                            'sdi' => $lastInvoice ? ($lastInvoice->sdi ?: '') : '',
                        ]);

                        $order = Order::create([
                            'user_id' => $user->id,
                            'status' => 2,
                            'total' => $event->price,
                            'payment_method' => 'free',
                            'order_number' => Str::orderedUuid(),
                            'result' => '{}',
                            'invoice_id' => $invoice->id,
                        ]);

                        $order->items()->create([
                            'product_type' => 'event_participation',
                            'product_name' => $event->name,
                            'product_code' => $event->id,
                            'quantity' => 1,
                            'price' => $event->price,
                            'vat' => 0,
                            'total' => $event->price
                        ]);
                    }

                    Log::info('Free event participant added. ', [
                        'user_id' => $waitingListItem->user_id,
                        'event_id' => $waitingListItem->event->id,
                    ]);

                    $waitingListItem->delete();

                    // Inviare comunicazione all'utente
                    event(new EventPaid($order, $event));

                } else {
                    // Imposta in modo che possa pagare
                    $waitingListItem->is_waiting_payment = true;
                    $waitingListItem->payment_deadline = now()->addDays(5);
                    $waitingListItem->save();
                    
                    // Inviare comunicazione all'utente

                }

                
                // Update for the cycle
                $waitingPayments = EventWaitingList::where('event_id', $event->id)->where('is_waiting_payment', true)->count();
                if($event->resultType() === 'enabling'){
                    $hasFreeSpots = ($event->instructorResults()->count() + $waitingPayments) < $event->max_participants;
                } else if($event->resultType() === 'ranking'){
                    $hasFreeSpots = ($event->results()->count() + $waitingPayments) < $event->max_participants;
                }
            } else {

                Log::error('No waiting list item found. ', [
                    'event_id' => $event->id,
                    'max_participants' => $event->max_participants,
                    'has_free_spots' => $hasFreeSpots,
                    'waiting_list_length' => EventWaitingList::where('event_id', $event->id)->count() > 0,
                    'ranking_participants' => $event->results()->count(),
                    'enabling_participants' => $event->instructorResults()->count(),
                ]);
                break;

            }

        }
    }
}