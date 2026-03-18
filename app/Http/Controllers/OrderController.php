<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Fee;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Str;

class OrderController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        //
        $query = Order::query()->orderBy('created_at', 'desc');

        // Filtro prezzo minimo
        if ($request->filled('min_price')) {
            $query->where('total', '>=', $request->input('min_price'));
        }

        // Filtro prezzo massimo
        if ($request->filled('max_price')) {
            $query->where('total', '<=', $request->input('max_price'));
        }

        // Filtro data minima
        if ($request->filled('min_date')) {
            $query->whereDate('created_at', '>=', $request->input('min_date'));
        }

        // Filtro data massima
        if ($request->filled('max_date')) {
            $query->whereDate('created_at', '<=', $request->input('max_date'));
        }

        // Filtro stati
        if ($request->filled('status')) {
            $query->whereIn('status', $request->input('status'));
        }

        $orders = $query->get();

        foreach ($orders as $key => $order) {
            $orders[$key]->status = __('orders.status' . $order->status);
            $orders[$key]->payment_method = __('orders.' . $order->payment_method);
            $orders[$key]->total = '€ ' . number_format($order->total, 2);
            $orders[$key]->user_fullname = $order->user->name . ' ' . $order->user->surname;
        }

        return view('orders.index', [
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
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
    public function show(string $id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order) {
        //

        $order->status_label = __('orders.status' . $order->status);
        $order->payment_method_label = __('orders.' . $order->payment_method);
        $order->total = '€ ' . number_format($order->total, 2);

        $order->invoice->address = json_decode($order->invoice->address);

        foreach ($order->items as $key => $item) {
            $order->items[$key]->total = '€ ' . number_format($item->total, 2);
            $order->items[$key]->product_name = $item->product_type ==  'event_participation'
                ? __('events.event_participation') . ' - ' . $item->product_name
                : __('orders.' . $item->product_name);
        }

        return view('orders.edit', [
            'order' => $order
        ]);
    }

    public function result(Order $order) {

        $payment_result = json_decode($order->result);

        return response()->json($payment_result);
    }

    public function invoice(Order $order, Request $request) {
        //

        $address = json_encode([
            'address' => $request->address,
            'zip' => $request->zip,
            'city' => $request->city,
            'country' => $request->country,
        ]);

        $invoice = Invoice::find($order->invoice_id);

        $invoice->address = $address;
        $invoice->name = $request->name;
        $invoice->surname = $request->surname;
        $invoice->vat = $request->vat;
        $invoice->business_name	= $request->business_name;
        $invoice->sdi = $request->sdi;

        $invoice->save();

        return redirect()->route('orders.edit', $order->id)->with('success', 'Invoice updated successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }

    public function successUserWireTransfer(Order $order) {
        return view('website.shop.wire-transfer', [
            'order' => $order
        ]);
    }

    public function approveWireTransfer(Order $order) {

        if ($order->status !== 0) {
            return redirect()->route('orders.edit', $order->id)->with('error', 'Order already approved');
        }

        $isBulkFeeOrder = ! is_null($order->academy_id);
        $athleteAcademy = $order->user->primaryAcademyAthlete();
        $academyId = $isBulkFeeOrder ? $order->academy_id : ($athleteAcademy ? $athleteAcademy->id : 1);

        foreach ($order->items as $item) {
            if ($item->product_type == 'fee') {
                if ($isBulkFeeOrder) {
                    for ($i = 0; $i < $item->quantity; $i++) {
                        Fee::create([
                            'user_id' => $order->user_id,
                            'academy_id' => $academyId,
                            'type' => 3,
                            'start_date' => now(),
                            'end_date' => now()->addYear(),
                            'auto_renew' => 0,
                            'unique_id' => Str::orderedUuid(),
                        ]);
                    }

                    continue;
                }

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

                $order->user->update([
                    'has_paid_fee' => 1,
                ]);
            } else if ($item->product_type == 'event_participation') {
                $event = Event::find($item->product_code);

                if ($event->resultType() === 'enabling') {
                    $event->instructorResults()->create([
                        'user_id' => $order->user_id,
                        'weapon_form_id' => $event->weapon_form_id,
                    ]);
                } else if ($event->resultType() === 'ranking') {
                    $event->results()->create([
                        'user_id' => $order->user_id,
                        'war_points' => 0,
                        'style_points' => 0,
                        'total_points' => 0,
                    ]);
                }
            }
        }

        if ($isBulkFeeOrder) {
            event(new \App\Events\BulkFeePaid($order));
        } else if ($order->items->contains('product_type', 'fee')) {
            event(new \App\Events\FeePaid($order));
        }

        $order->update([
            'status' => 2,
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('orders.edit', $order->id)->with('success', 'Order approved successfully');
        
    }
}
