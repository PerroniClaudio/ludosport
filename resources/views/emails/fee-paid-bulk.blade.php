<x-mail::message>

# {{ __('emails.bulk_fee_email_title', ['name' => $order->user->name]) }}

{{ __('emails.bulk_fee_email_introduction') }}

## {{ __('emails.bulk_fee_email_order_details') }}

-   **{{ __('emails.bulk_fee_email_order_number') }}**: {{ $order->order_number }}
-   **{{ __('emails.bulk_fee_email_order_date') }}**: {{ $order->created_at->format('d/m/Y') }}
-   **{{ __('emails.bulk_fee_email_total') }}**: {{ '€ ' . number_format($order->total, 2) . '' }}
-   **{{ __('emails.bulk_fee_email_payment_method') }}**: {{ __('orders.'.$order->payment_method) }}

## {{ __('emails.bulk_fee_email_fee_purchased_items') }}

@foreach ($order->items as $item)

-   **{{ __('orders.'.$item->product_name) }}** x {{ $item->quantity }} - {{ '€ ' . number_format($item->total, 2) }}

@endforeach

## {{ __('emails.bulk_fee_email_fee_validity_title') }}

{{ __('emails.bulk_fee_email_fee_validity_text', ['date' => '31/08/' . date('Y', strtotime('+1 year'))]) }}

{{ __('emails.fee_email_regards') }}
{{ config('app.name') }}

</x-mail::message>
