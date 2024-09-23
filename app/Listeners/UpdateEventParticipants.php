<?php

namespace App\Listeners;

use App\Events\ParticipantsUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Event;
use App\Models\EventWaitingList;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

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

        $hasFreeSpots = false;
        if($event->resultType() === 'enabling'){
            $hasFreeSpots = $event->instructorResults()->count() < $event->max_participants;
        } else if($event->resultType() === 'ranking'){
            $hasFreeSpots = $event->results()->count() < $event->max_participants;
        }
        
        // Check if the event has free spots and if there are users in the waiting list
        while ($hasFreeSpots && (EventWaitingList::where('event_id', $event->id)->count() > 0)) {
            // Picks the first element in the waiting list
            $waitingListItem = EventWaitingList::where('event_id', $event->id)->first();
            
            if($waitingListItem) {

                // Check if payment is required
                if($event->is_free) {

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

                    $waitingListItem->delete();

                } else {
                    
                    // check payment method
                    if($waitingListItem->order->payment_method === 'paypal') {
                        $provider = new PaypalClient;
                        $provider->setApiCredentials(config('paypal'));
                        $accessToken = $provider->getAccessToken();
                
                        $order = $waitingListItem->order;
                
                        $details = $provider->showOrderDetails($order->paypal_order_id);

                        $createTime = new \DateTime($details['purchase_units'][0]['payments']['authorizations'][0]['create_time']);
                        $expirationTime = new \DateTime($details['purchase_units'][0]['payments']['authorizations'][0]['expiration_time']);
                        $currentTime = new \DateTime();

                        $interval = $createTime->diff($currentTime);
                        $daysPassed = $interval->days;

                        $result=null;

                        // Update della valuta nel provider. altrimenti può dare errore
                        // $paypalConfig = array_merge(config('paypal'), ['currency' => $details['purchase_units'][0]['amount']['currency_code']]);
                        // $provider->setApiCredentials($paypalConfig);

                        // Paypal ha un tempo di autorizzazione di 29 giorni.
                        // Inoltre c'è l'honor period che dura 4 giorni dalla data iniziale e 3 giorni dal rinnovo autorizzazione.
                        // Al di fuori dell'honor period non si può fare il capture e si deve prima rinnovare l'autorizzazione. 
                        if( $expirationTime < $currentTime ) {
                            // Scaduto il tempo massimo per l'autorizzazione. non possiamo rinnoverla in autonomia.
                            Log::error('Authorization expired. ', [
                                'order_id' => $order->id,
                                'event_id' => $waitingListItem->event->id,
                                'event_waiting_list_id' => $waitingListItem->id,
                            ]);
                            $order->update(['status' => 4]); // Stato fallito o annullato
                            $waitingListItem->delete();
                            continue;
                        } else if ( $daysPassed >= 4 ) {
                            // Scaduto l'honor period, ma possiamo rinnovare l'autorizzazione e fare il capture in autonomia.
                            // DA TESTARE PERCHÈ NON HO UNA PREAUTORIZZAZIONE CON HONOR PERIOD SCADUTO
                            $refreshResult = $provider->reAuthorizeAuthorizedPayment(
                                $details['purchase_units'][0]['payments']['authorizations'][0]['id'], 
                                $details['purchase_units'][0]['amount']['value']
                            );

                            if(isset($refreshResult['id']) && !is_null($refreshResult['id'])) {
                                // l'id dovrebbe essere quello dato dalla nuova autorizzazione.
                                $result = $provider->captureAuthorizedPayment(
                                    $refreshResult['id'],
                                    $order->invoice_id ?? '',
                                    $details['purchase_units'][0]['amount']['value'],
                                    'Finalized payment for LudoSport event "' . $waitingListItem->event->name . '"',
                                );
                            }
                        } else {
                            // Si può fare il capture senza aggiornare la preautorizzazione
                            $result = $provider->captureAuthorizedPayment(
                                $details['purchase_units'][0]['payments']['authorizations'][0]['id'],
                                $order->invoice_id ?? '',
                                $details['purchase_units'][0]['amount']['value'],
                                'Finalized payment for LudoSport event "' . $waitingListItem->event->name . '"',
                            );
                        }

                        Log::info($result);
                        exit(); 

                        if ($result['status'] === 'COMPLETED') {
                            
                            Log::info('PayPal preauthorized payment capture success. ', [
                                'order_id' => $order->id,
                            ]);
    
                            $order->update(['status' => 2, 'result' => json_encode($result)]);
                    
                            $event = $waitingListItem->event;
                    
                            if($event->resultType() === 'enabling') {
                                $event->instructorResults()->create([
                                    'user_id' => $order->user_id,
                                    'weapon_form_id' => $event->weapon_form_id,
                                ]);
                            } else if($event->resultType() === 'ranking') {
                                $event->results()->create([
                                    'user_id' => $order->user_id,
                                    'war_points' => 0,
                                    'style_points' => 0,
                                    'total_points' => 0,
                                ]);
                            }
                
                            $waitingListItem->delete();
                        } else {
                            Log::error('PayPal preauthorized payment capture failed. ', [
                                'order_id' => $order->id,
                                'event_id' => $waitingListItem->event->id,
                                'event_waiting_list_id' => $waitingListItem->id,
                            ]);
                            $order->update(['status' => 4, 'result' => json_encode($result)]); // Stato fallito o annullato
                            $waitingListItem->delete();
                            continue;
                        }
                    } else if ($waitingListItem->order->payment_method === 'stripe') {
                        // $order = Order::where('stripe_payment_intent_id', $waitingListItem->order->stripe_payment_intent_id)->first();
                        // $order->update(['status' => 2]);
                        // $event = $waitingListItem->event;
                
                        // if($event->resultType() === 'enabling') {
                        //     $event->instructorResults()->create([
                        //         'user_id' => $order->user_id,
                        //         'weapon_form_id' => $event->weapon_form_id,
                        //     ]);
                        // } else if($event->resultType() === 'ranking') {
                        //     $event->results()->create([
                        //         'user_id' => $order->user_id,
                        //         'war_points' => 0,
                        //         'style_points' => 0,
                        //         'total_points' => 0,
                        //     ]);
                        // }
                
                        // $waitingListItem->delete();
                
                        // return response()->json(['success' => true]);
                        Log::error('Stripe payment method not implemented. ', [
                            'order_id' => $waitingListItem->order->id,
                        ]);
                        break;
                    }

                }

                
                // Update for the cycle
                if($event->resultType() === 'enabling'){
                    $hasFreeSpots = $event->instructorResults()->count() < $event->max_participants;
                } else if($event->resultType() === 'ranking'){
                    $hasFreeSpots = $event->results()->count() < $event->max_participants;
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