<x-mail::message>

# {{ __('emails.fee_email_title', ['name' => $order->user->name]) }}

{{ __('emails.fee_email_introduction') }}

## {{ __('emails.fee_email_order_details') }}

-   **{{ __('emails.fee_email_order_number') }}**: {{ $order->order_number }}
-   **{{ __('emails.fee_email_order_date') }}**: {{ $order->created_at->format('d/m/Y') }}
-   **{{ __('emails.fee_email_membership_type') }}**: {{ __('orders.'.$order->items()->first()->product_name) }}
-   **{{ __('emails.fee_email_total') }}**: {{ '€ ' . number_format($order->total, 2) . '' }}
-   **{{ __('emails.fee_email_payment_method') }}**: {{ __('orders.'.$order->payment_method) }}

## {{ __('emails.fee_email_fee_validity_title') }}

{{ __('emails.fee_email_fee_validity_text', ['date' => '31/08/' . date('Y', strtotime('+1 year'))]) }}

## {{ __('emails.fee_email_how_to_log_in') }}

{{ __('emails.fee_email_log_in_instructions') }}

<x-mail::button url="{{ route('login') }}" >
Login
</x-mail::button>

{{ __('emails.fee_email_log_in_instructions_alt') }} [{{ route('login') }}]({{ route('login') }})

{{ __('emails.fee_email_regards') }}
{{ config('app.name') }}

</x-mail::message>
