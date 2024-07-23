<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;

class OrderController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //

        $orders = Order::orderBy('created_at', 'desc')->get();

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

        $order->status = __('orders.status' . $order->status);
        $order->payment_method = __('orders.' . $order->payment_method);
        $order->total = '€ ' . number_format($order->total, 2);

        $order->invoice->address = json_decode($order->invoice->address);

        foreach ($order->items as $key => $item) {
            $order->items[$key]->total = '€ ' . number_format($item->total, 2);
            $order->items[$key]->product_name = __('orders.' . $item->product_name);
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
}
