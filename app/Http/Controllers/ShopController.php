<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends Controller {
    //

    public function shop() {
        return view('website.shop');
    }

    public function activate() {

        $user = User::find(Auth()->user()->id);

        if ($user->has_paid_fee === 1) {
            return redirect()->route('shop')->with('error', __('website.already_paid'));
        }

        $invoice = $user->invoices->first();

        if (!$invoice) {
            $invoice = $user->invoices()->create([
                'user_id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname ? $user->surname : '',
                'address' => json_encode([
                    'address' => '',
                    'zip' => '',
                    'city' => '',
                    'country' => 'Italy',
                ]),
                'vat' => '',
                'sdi' => '',
            ]);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 0,
            'total' => 0,
            'payment_method' => '',
            'order_number' => Str::orderedUuid(),
            'result' => '{}',
            'invoice_id' => $invoice->id,
        ]);

        session(['order_id' => $order->id]);

        return view('website.shop.activate-membership', [
            'order' => $order,
            'invoice' => $invoice,
        ]);
    }
}
