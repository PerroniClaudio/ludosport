<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromArray;

class OrdersExport implements FromArray {
    private $export;

    public function __construct($export) {
        $this->export = $export;
    }

    public function array(): array {

        //
        $filters = json_decode($this->export->filters);

        $orders = Order::where('created_at', '>=', $filters->start_date)->where('created_at', '<=', $filters->end_date)->with([
            'user',
            'invoice',
            'items'
        ])->get()->map(function ($order) {

            $address = json_decode($order->invoice->address);

            return [
                $order->id,
                $order->order_number,
                $order->user->unique_code,
                $order->invoice->name,
                $order->invoice->surname,
                $order->user->email,
                $order->payment_method,
                $order->total,
                __('orders.status' . $order->status),
                $order->invoice->want_invoice ? 'Yes' : 'No',
                $address->address,
                $address->city,
                $address->zip,
                $address->country,
                $order->invoice->is_business ? 'Yes' : 'No',
                $order->invoice->business_name,
                $order->invoice->vat,
                $order->invoice->sdi,
                $order->created_at,
                $order->items->map(function ($item) {
                    // return __('orders.' . $item->product_name) . " x " . $item->quantity . " - " . $item->total . "€\n";
                    return ($item->product_type ==  'event_participation'
                    ? __('events.event_participation') . ' - ' . $item->product_name
                    : __('orders.' . $item->product_name));
                })->implode(', '),

            ];
        })->toArray();

        $headers = [
            "ID",
            "Order Number",
            "User Code",
            "Name",
            "Surname",
            "Email",
            "Payment Method",
            "Total",
            "Status",
            "Want Invoice",
            "Address",
            "City",
            "Zip",
            "Country",
            "Is Business",
            "Business Name",
            "VAT",
            "SDI",
            "Created At",
            "Items"
        ];


        return [
            $headers,
            $orders
        ];
    }
}
