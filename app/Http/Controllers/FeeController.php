<?php

namespace App\Http\Controllers;

use App\Events\FeePaid;
use App\Models\Academy;
use App\Models\Fee;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PaypalClient;

class FeeController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $user = User::find(Auth::user()->id);
        $academy_id = $user->getActiveInstitutionId() ?? null;

        if (!$user->validatePrimaryInstitutionPersonnel()) {
            return redirect()->route('dashboard')->with('error', 'Main academy not found');
        }

        $fees = Fee::where('academy_id', $academy_id)->where([
            ['used', '=', 0],
            ['end_date', '>', now()->format('Y-m-d')],
            ['academy_id', '=', $academy_id],
        ])->count();

        $athletes_no_fees = Academy::find($academy_id)->athletes()->where('has_paid_fee', 0)->get();

        foreach ($athletes_no_fees as $key => $value) {
            $athletes_no_fees[$key]->fullname = $value->name . ' ' . $value->surname;
        }

        return view('fees.rector.index', [
            'fees_number' => $fees,
            'athletes_no_fees' => $athletes_no_fees,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //

        $user = User::find(Auth::user()->id);
        $academy = $user->getActiveInstitutionId() ?? null;

        if (!$academy) {
            return redirect()->route('dashboard')->with('error', 'Main academy academy not found');
        }

        $fee_price = config('app.stripe.fee_price_numeral');

        return view('fees.rector.create', [
            'academy' => $academy,
            'fee_price' => $fee_price,
        ]);
    }

    public function renew() {
        //

        $user = User::find(Auth::user()->id);
        $academy_id = $user->getActiveInstitutionId() ?? null;

        if (!$academy_id) {
            return redirect()->route('dashboard')->with('error', 'Main academy academy not found');
        }

        $fees = Fee::where('academy_id', $academy_id)->where([
            ['used', '=', 0],
            ['end_date', '>', now()->format('Y-m-d')],
            ['academy_id', '=', $academy_id],
        ])->count();

        $academy = Academy::find($academy_id);
        $users = $academy->athletes()->where('has_paid_fee', 1)->get();
        $users_expired_fees = [];

        foreach ($users as $key => $value) {

            $latest_fee = $value->fees()->latest()->first();

            if ($latest_fee && ($latest_fee->end_date < Carbon::now())) {
                $users_expired_fees[] = [
                    'id' => $value->id,
                    'name' => $value->name,
                    'surname' => $value->surname,
                    'fullname' => $value->name . ' ' . $value->surname,
                    'email' => $value->email,
                    'fee_expired_at' => $latest_fee->end_date,
                ];
            }
        }


        return view('fees.rector.renew', [
            'users_expired_fees' => $users_expired_fees,
            'fees_number' => $fees,
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

        $user = User::find(Auth::user()->id);
        $academy_id = $user->getActiveInstitutionId() ?? null;

        $available_fees = Fee::where('academy_id', $academy_id)
            ->where('used', 0)
            ->where('end_date', '>', now()->format('Y-m-d'))
            ->count();

        $fees_consumed = 0;

        $selected_users = json_decode($request->selected_users);

        foreach ($selected_users as $id) {
            $fees_consumed++;
        }

        return response()->json([
            'available_fees' => $available_fees,
            'fees_consumed' => $fees_consumed,
            'is_fees_needed' => $fees_consumed > $available_fees,
        ]);
    }

    public function associateFeesToUsers(Request $request) {

        $authuser = User::find(Auth::user()->id);

        $selected_users = json_decode($request->selected_users);

        DB::beginTransaction();

        foreach ($selected_users as $id) {
            $user = User::find($id);

            $availableFee = Fee::where([
                ['used', '=', 0],
                ['end_date', '>', now()->format('Y-m-d')],
                ['academy_id', '=', $authuser->getActiveInstitutionId() ?? 1],
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
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'is_error' => false,
            'success' => 'Fees associated successfully',
        ]);
    }

    public function academyAvailableFees(Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        if ($authRole != 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $academy = Academy::find($request->academy_id);

        if (!$academy) {
            return response()->json(['error' => 'Academy not found'], 404);
        }

        $fees = Fee::where('academy_id', $academy->id)
            ->where('end_date', '>', now()->format('Y-m-d'))
            ->where('used', 0)
            ->get();

        return response()->json([
            'fees' => $fees,
            'count' => $fees->count(),
        ]);
    }

    public function createFreeFeesForAcademy(Request $request) {
        $authUser = User::find(Auth::user()->id);
        $authRole = $authUser->getRole();

        if ($authRole != 'admin') {
            return redirect()->back()->withErrors(['new_fees_error' => 'Unauthorized']);
        }

        $academy = Academy::find($request->academy_id);

        if (!$academy) {
            return redirect()->back()->withErrors(['academy' => 'Academy not found']);
        }

        for ($index = 0; $index < $request->number; $index++) {
            Fee::create([
                'user_id' => 0,
                'academy_id' => $academy->id,
                'type' => 3,
                'start_date' => now(),
                'end_date' => now()->addYear()->endOfYear()->format('Y') . '-08-31',
                'auto_renew' => 0,
                'unique_id' => Str::orderedUuid(),
                'used' => 0,
            ]);
        }

        return back()->with('success', 'Fees created successfully');
    }

    //? Checkout with Stripe.

    public function checkoutStripe(Request $request) {

        // Crea ordine

        $user = User::find(Auth::user()->id);
        $invoice = $user->invoices()->latest()->first();

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

        $product_code = config('app.stripe.fee_code');
        $price_id = config('app.stripe.fee_price');
        $price = $this->retrievePriceByPriceId($price_id);

        foreach ($items as $item) {

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

        $user = User::find(Auth::user()->id);

        $order_id = $request->session()->get('order_id');
        $order = Order::findOrFail($order_id);

        // Aggiungi item all'ordine

        $items = json_decode($request->items);
        $prices = [];

        $product_code = config('app.stripe.fee_code');
        $price_id = config('app.stripe.fee_price');
        $price = $this->retrievePriceByPriceId($price_id);

        foreach ($items as $item) {

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
     * Payment success. - Personnel (rector)
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
                $primaryAcademy = $user->primaryAcademy();
                if (!$primaryAcademy) {
                    Log::error('Primary academy not found. Check for fees created for this order. - Order ID: ' . $order->id . ' - Item ID: ' . $item->id);
                }

                for ($i = 0; $i < $item->quantity; $i++) {
                    Fee::create([
                        'user_id' => $order->user_id,
                        'academy_id' => $order->user->getActiveInstitutionId() ?? 1,
                        'type' => 3,
                        'start_date' => now(),
                        'end_date' => now()->addYear()->endOfYear()->format('Y') . '-08-31',
                        'auto_renew' => 0,
                        'unique_id' => Str::orderedUuid(),
                    ]);
                }
            }

            event(new \App\Events\BulkFeePaid($order));
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

            $academy = $order->user->primaryAcademyAthlete();
            $academyId = $academy->id ?? 1;

            foreach ($order->items as $item) {

                Fee::create([
                    'user_id' => $order->user_id,
                    'academy_id' => $academyId,
                    'type' => 3,
                    'start_date' => now(),
                    'end_date' => now()->addYear()->endOfYear()->format('Y') . '-08-31',
                    'auto_renew' => 0,
                    'used' => 1,
                    'unique_id' => Str::orderedUuid(),
                ]);
            }

            $user->update([
                'has_paid_fee' => 1,
            ]);

            event(new FeePaid($order));
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

    public function checkoutPaypal(Request $request) {
        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        // Crea ordine

        $user = User::find(Auth::user()->id);
        $invoice = $user->invoices()->latest()->first();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 0,
            'total' => 0,
            'payment_method' => 'paypal',
            'order_number' => Str::orderedUuid(),
            'result' => '{}',
            'invoice_id' => $invoice->id,
        ]);

        // Aggiungi item all'ordine

        $items = json_decode($request->items);
        $amount = 0;

        $price = config('app.stripe.fee_price_numeral');

        foreach ($items as $item) {

            $amount += $price;

            $order->items()->create([
                'product_type' => 'fee',
                'product_name' => $item->name,
                'product_code' => 'fee',
                'quantity' => $item->quantity,
                'price' => number_format($price * $item->quantity, 2),
                'vat' => 0,
                'total' => number_format($price * $item->quantity, 2),
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
                'cancel_url' => route('fees.paypal-cancel') . "?order_id={$order->id}",
                'return_url' => route('fees.paypal-success') . "?order_id={$order->id}",
                'order_id' => $order->id
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
                'url' => route('fees.paypal-cancel') . "?order_id={$order->id}",
            ]);
        }
    }

    public function userCheckoutPaypal(Request $request) {

        $provider = new PaypalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $order_id = $request->session()->get('order_id');
        $order = Order::findOrFail($order_id);

        $items = json_decode($request->items);
        $amount = 0;
        $feePrice = config('app.stripe.fee_price_numeral');
        foreach ($items as $item) {
            $amount += $feePrice;

            $order->items()->create([
                'product_type' => 'fee',
                'product_name' => $item->name,
                'product_code' => 'fee',
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

            $academy = $order->user->primaryAcademyAthlete();
            $academyId = $academy->id ?? 1;

            foreach ($order->items as $item) {

                Fee::create([
                    'user_id' => $order->user_id,
                    'academy_id' => $academyId,
                    'type' => 3,
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

            event(new FeePaid($order));
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

        if ($order->status !== 1) {
        } else {
            $order->update([
                'status' => 4,
                'result' => json_encode($result),
            ]);
        }

        return view('website.shop.fees-cancel', [
            'order' => $order,
        ]);
    }

    public function successPaypal(Request $request) {
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

            foreach ($order->items as $item) {

                for ($i = 0; $i < $item->quantity; $i++) {
                    Fee::create([
                        'user_id' => $order->user_id,
                        'academy_id' => $order->user->getActiveInstitutionId() ?? 1,
                        'type' => 3,
                        'start_date' => now(),
                        'end_date' => now()->addYear(),
                        'auto_renew' => 0,
                        'unique_id' => Str::orderedUuid(),
                    ]);
                }
            }

            event(new \App\Events\BulkFeePaid($order));
        }

        return view('fees.rector.success', [
            'order' => $order,
        ]);
    }

    public function cancelPaypal(Request $request) {
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
                'status' => 4,
                'result' => json_encode($result),
            ]);
        }

        return view('fees.rector.cancel', [
            'order' => $order,
        ]);
    }

    //? Wire Transfer.

    public function userCheckoutWireTransfer(Request $request) {

        // Crea ordine

        $user = User::find(Auth::user()->id);
        $invoice = $user->invoices()->latest()->first();

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 0,
            'total' => 0,
            'payment_method' => 'wire_transfer',
            'order_number' => Str::orderedUuid(),
            'result' => '{}',
            'invoice_id' => $invoice->id,
        ]);


        $items = json_decode($request->items);
        $amount = 0;

        $price = config('app.stripe.fee_price_numeral');

        foreach ($items as $item) {

            $amount += $price;

            $order->items()->create([
                'product_type' => 'fee',
                'product_name' => $item->name,
                'product_code' => 'fee',
                'quantity' => $item->quantity,
                'price' => number_format($price * $item->quantity, 2),
                'vat' => 0,
                'total' => number_format($price * $item->quantity, 2),
            ]);
        }

        $order->update([
            'total' => $amount,
        ]);

        return redirect()->route('shop.wire-transfer-success', $order->id);
    }
}
