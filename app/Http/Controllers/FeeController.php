<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Fee;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\DB;
use Srmklive\PayPal\Services\Paypal as PaypalClient;

class FeeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $user = User::find(Auth()->user()->id);
        $academy_id = $user->academies()->first()->id;


        $senior_fees = Fee::where('academy_id', $academy_id)->where([
            ['type', '=',  1],
            ['used', '=', 0],
            ['end_date', '>', now()->format('Y-m-d')],
            ['academy_id', '=', $user->academies()->first()->id],
        ])->count();
        $junior_fees = Fee::where('academy_id', $academy_id)->where([
            ['type', '=',  2],
            ['used', '=', 0],
            ['end_date', '>', now()->format('Y-m-d')],
            ['academy_id', '=', $user->academies()->first()->id],
        ])->count();
        $athletes_no_fees = Academy::find($academy_id)->athletes()->where('has_paid_fee', 0)->get();

        foreach ($athletes_no_fees as $key => $value) {
            $athletes_no_fees[$key]->fullname = $value->name . ' ' . $value->surname;

            $today = now();
            $athlete_birthday = Carbon::parse($value->birthday);
            $athlete_age = abs(number_format($today->diffInYears($athlete_birthday)));

            $athletes_no_fees[$key]->type_needed = $athlete_age > 16 ? __('fees.senior_fees') : __('fees.junior_fees');
        }


        return view('fees.rector.index', [
            'senior_fees_number' => $senior_fees,
            'junior_fees_number' => $junior_fees,
            'athletes_no_fees' => $athletes_no_fees,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $user = User::find(Auth()->user()->id);
        $academy = $user->academies()->first()->id;

        return view('fees.rector.create', [
            'academy' => $academy,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Fee $fee) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Fee $fee) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fee $fee) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fee $fee) {
        //
    }

    public function extimateFeeConsumption(Request $request) {

        $user = User::find(Auth()->user()->id);
        $academy_id = $user->academies()->first()->id;


        $senior_fees = Fee::where('academy_id', $academy_id)->where([
            ['type', '=',  1],
            ['used', '=', 0],
            ['end_date', '>', now()->format('Y-m-d')],
            ['academy_id', '=', $user->academies()->first()->id],
        ])->count();
        $junior_fees = Fee::where('academy_id', $academy_id)->where([
            ['type', '=',  2],
            ['used', '=', 0],
            ['end_date', '>', now()->format('Y-m-d')],
            ['academy_id', '=', $user->academies()->first()->id],
        ])->count();
        $senior_fees_consumed = 0;
        $junior_fees_consumed = 0;

        $selected_users = json_decode($request->selected_users);

        foreach ($selected_users as $id) {
            $user = User::find($id);

            $today = now();
            $athlete_birthday = Carbon::parse($user->birthday);
            $athlete_age = abs(number_format($today->diffInYears($athlete_birthday)));

            if ($athlete_age > 16) {
                $senior_fees_consumed++;
            } else {
                $junior_fees_consumed++;
            }
        }

        return response()->json([
            'senior_fees' => $senior_fees,
            'junior_fees' => $junior_fees,
            'senior_fees_consumed' => $senior_fees_consumed,
            'junior_fees_consumed' => $junior_fees_consumed,
            'is_junior_fees_needed' => $junior_fees_consumed > $junior_fees,
            'is_senior_fees_needed' => $senior_fees_consumed > $senior_fees,
        ]);
    }

    public function associateFeesToUsers(Request $request) {

        $authuser = User::find(Auth()->user()->id);

        $selected_users = json_decode($request->selected_users);
        $now_date = now()->format('Y-m-d');

        DB::beginTransaction();

        foreach ($selected_users as $id) {
            $user = User::find($id);

            $today = now();
            $athlete_birthday = Carbon::parse($user->birthday);
            $athlete_age = abs(number_format($today->diffInYears($athlete_birthday)));

            if ($athlete_age > 16) {
                $type = 1;
            } else {
                $type = 2;
            }

            $availableFee = Fee::where([
                ['type', '=',  $type],
                ['used', '=', 0],
                ['end_date', '>', now()->format('Y-m-d')],
                ['academy_id', '=', $authuser->academies()->first()->id],
            ])->first();


            if ($availableFee) {

                $availableFee->update([
                    'user_id' => $user->id,
                    'used' => true,
                    'end_date' => now()->addYear()->endOfYear()->format('Y') . '-08-31',
                ]);

                $user->update([
                    'active_fee_id' => $availableFee->id,
                    'has_paid_fee' => 1,
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'is_error' => true,
                    'error' => 'No available fees',
                    'type' => $type,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'is_error' => false,
            'success' => 'Fees associated successfully',
        ]);
    }

    //? Checkout with Stripe.

    public function checkoutStripe(Request $request) {

        // Crea ordine

        $user = User::find(Auth()->user()->id);
        $invoice = $user->invoices->first();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 0,
            'total' => 0,
            'payment_method' => 'stripe',
            'order_number' => Str::orderedUuid(),
            'result' => '{}',
            'invoice_id' => $invoice->id,
        ]);

        // Aggiungi item all'ordine

        $items = json_decode($request->items);
        $prices = [];

        foreach ($items as $item) {

            $product_code = $item->name == 'senior_fee' ? Env('STRIPE_SENIOR_FEE_CODE') : Env('STRIPE_JUNIOR_FEE_CODE');
            $price_id = $item->name == 'senior_fee' ? Env('STRIPE_SENIOR_FEE_PRICE') : Env('STRIPE_JUNIOR_FEE_PRICE');
            $price = $this->retrievePriceByPriceId($price_id);

            $order->items()->create([
                'product_type' => 'fee',
                'product_name' => $item->name,
                'product_code' => $product_code,
                'quantity' => $item->quantity,
                'price' => $price  / 100,
                'vat' => 0,
                'total' => ($price * $item->quantity) / 100,
            ]);

            $prices["{$price_id}"] =  "{$item->quantity}";
        }

        // Calcola totale

        $total = $order->items()->sum('total');

        $order->update([
            'total' => $total,
        ]);

        return $request->user()->checkout($prices, [
            'success_url' => route('fees.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('fees.cancel')  . '?session_id={CHECKOUT_SESSION_ID}',
            'metadata' => ['order_id' => $order->id],
        ]);
    }

    public function userCheckoutStripe(Request $request) {

        $user = User::find(Auth()->user()->id);

        $order_id = $request->session()->get('order_id');
        $order = Order::findOrFail($order_id);

        // Aggiungi item all'ordine

        $items = json_decode($request->items);
        $prices = [];

        foreach ($items as $item) {

            $product_code = $item->name == 'senior_fee' ? Env('STRIPE_SENIOR_FEE_CODE') : Env('STRIPE_JUNIOR_FEE_CODE');
            $price_id = $item->name == 'senior_fee' ? Env('STRIPE_SENIOR_FEE_PRICE') : Env('STRIPE_JUNIOR_FEE_PRICE');
            $price = $this->retrievePriceByPriceId($price_id);

            $order->items()->create([
                'product_type' => 'fee',
                'product_name' => $item->name,
                'product_code' => $product_code,
                'quantity' => $item->quantity,
                'price' => $price  / 100,
                'vat' => 0,
                'total' => ($price * $item->quantity) / 100,
            ]);

            $prices["{$price_id}"] =  "{$item->quantity}";
        }

        $total = $order->items()->sum('total');

        $order->update([
            'total' => $total,
        ]);

        return $request->user()->checkout($prices, [
            'success_url' => route('shop.fees.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('shop.fees.cancel')  . '?session_id={CHECKOUT_SESSION_ID}',
            'metadata' => ['order_id' => $order->id],
        ]);
    }


    /**
     * Payment success.
     */

    public function success(Request $request) {
        $sessionId = $request->get('session_id');

        if ($sessionId === null) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            return;
        }

        $orderId = $session['metadata']['order_id'] ?? null;
        $order = Order::findOrFail($orderId);

        if ($order->status !== 0) {
        } else {

            $order->update(['status' => 2, 'result' => json_encode($session)]);
            $user = User::find($order->user_id);

            foreach ($order->items as $item) {

                for ($i = 0; $i < $item->quantity; $i++) {
                    Fee::create([
                        'user_id' => $order->user_id,
                        'academy_id' => $order->user->academies()->first()->id,
                        'type' => $item->product_name == 'senior_fee' ? 1 : 2,
                        'start_date' => now(),
                        'end_date' => now()->addYear(),
                        'auto_renew' => 0,
                        'unique_id' => Str::orderedUuid(),
                    ]);
                }
            }
        }


        return view('fees.rector.success', [
            'order' => $order,
        ]);
    }

    public function successUser(Request $request) {
        $sessionId = $request->get('session_id');

        if ($sessionId === null) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);
        $orderId = $session['metadata']['order_id'] ?? null;
        $order = Order::findOrFail($orderId);

        if ($order->status !== 0) {
        } else {

            $order->update([
                'status' => 2,
                'total' => number_format($session->amount_total / 100, 2),
                'payment_method' => 'stripe',
                'result' => json_encode($session)
            ]);
            $user = User::find($order->user_id);

            //! Cosa succede se l'utente non è in una accademia?

            $academy = $order->user->academies()->first() ? $order->user->academies()->first()->id : 1;

            foreach ($order->items as $item) {

                Fee::create([
                    'user_id' => $order->user_id,
                    'academy_id' => $academy ? $academy : 1,
                    'type' => $item->product_name == 'senior_fee' ? 1 : 2,
                    'start_date' => now(),
                    'end_date' => now()->addYear()->endOfYear()->format('Y') . '-08-31',
                    'auto_renew' => 1,
                    'used' => 1,
                    'unique_id' => Str::orderedUuid(),
                ]);
            }

            $user->update([
                'has_paid_fee' => 1,
            ]);
        }

        return view('website.shop.fees-success', [
            'order' => $order,
        ]);
    }

    /**
     * Payment cancel.
     */

    public function cancel(Request $request) {
        $sessionId = $request->get('session_id');

        if ($sessionId === null) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        $orderId = $session['metadata']['order_id'] ?? null;

        $order = Order::findOrFail($orderId);

        if ($order->status !== 0) {
        } else {
            $order->update(['status' => 4, 'result' => json_encode($session)]);
        }



        return view('fees.rector.cancel', [
            'order' => $order,
        ]);
    }

    public function cancelUser(Request $request) {
        $sessionId = $request->get('session_id');

        if ($sessionId === null) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        $orderId = $session['metadata']['order_id'] ?? null;

        $order = Order::findOrFail($orderId);

        if ($order->status !== 0) {
        } else {
            $order->update(['status' => 4, 'result' => json_encode($session)]);
        }

        return view('website.shop.fees-cancel', [
            'order' => $order,
        ]);
    }

    private function retrievePriceByPriceId($priceId) {
        $price = Cashier::stripe()->prices->retrieve($priceId);
        return $price->unit_amount;
    }


    //? Checkout with Paypal.

    public function userCheckoutPaypal(Request $request) {

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $order_id = $request->session()->get('order_id');
        $order = Order::findOrFail($order_id);

        $items = json_decode($request->items);
        $amount = 0;
        foreach ($items as $item) {
            $amount += $item->name == 'senior_fee' ? 50 : 25;

            $order->items()->create([
                'product_type' => 'fee',
                'product_name' => $item->name,
                'product_code' => $item->name == 'senior_fee' ? 'senior_fee' : 'junior_fee',
                'quantity' => $item->quantity,
                'price' => number_format($amount, 2),
                'vat' => 0,
                'total' => number_format($amount, 2),
            ]);
        }

        $response = $provider->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => number_format($amount, 2),
                    ],
                ],
            ],
            'application_context' => [
                'cancel_url' => route('shop.fees.paypal-cancel') . "?order_id={$order_id}",
                'return_url' => route('shop.fees.paypal-success') . "?order_id={$order_id}",
                'order_id' => $order_id
            ],
        ]);

        if (isset($response['id']) && $response['id'] !== null) {
            session(['paypal_order_id' => $response['id']]);

            foreach ($response['links'] as $link) {
                if ($link['rel'] == 'approve') {


                    $order->update([
                        'status' => 1,
                        'payment_method' => 'paypal',
                        'total' => number_format($amount, 2),
                        'result' => json_encode($response),
                    ]);

                    session()->put('product_name', $request->product_name);

                    return response()->json([
                        'success' => true,
                        'url' => $link['href'],
                    ]);
                }
            }
        } else {

            $order->update([
                'status' => 4,
                'payment_method' => 'paypal',
                'total' => number_format($amount, 2),
                'result' => 'Error creating order',
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error creating order',
                'url' => route('shop.fees.fees-cancel'),
            ]);
        }
    }

    public function successUserPaypal(Request $request) {
        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $result = $provider->capturePaymentOrder($request->token);
        $orderId = $request->order_id;


        if ($orderId === null) {
            return;
        }

        $order = Order::findOrFail($orderId);

        if ($order->status !== 1) {
        } else {

            $order->update([
                'status' => 2,
                'result' => json_encode($result),
            ]);
            $user = User::find($order->user_id);

            //! Cosa succede se l'utente non è in una accademia?

            $academy = $order->user->academies()->first() ? $order->user->academies()->first()->id : 1;

            foreach ($order->items as $item) {

                Fee::create([
                    'user_id' => $order->user_id,
                    'academy_id' => $academy ? $academy : 1,
                    'type' => $item->product_name == 'senior_fee' ? 1 : 2,
                    'start_date' => now(),
                    'end_date' => now()->addYear()->endOfYear()->format('Y') . '-08-31',
                    'auto_renew' => 1,
                    'used' => 1,
                    'unique_id' => Str::orderedUuid(),
                ]);
            }

            $user->update([
                'has_paid_fee' => 1,
            ]);
        }

        return view('website.shop.fees-success', [
            'order' => $order,
        ]);
    }

    public function cancelUserPaypal(Request $request) {
        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $result = $provider->capturePaymentOrder($request->token);
        $orderId = $request->order_id;

        if ($orderId === null) {
            return;
        }

        $order = Order::findOrFail($orderId);

        $order->update(['status' => 4, 'result' => json_encode($result)]);

        return view('website.shop.fees-cancel', [
            'order' => $order,
        ]);
    }
}
