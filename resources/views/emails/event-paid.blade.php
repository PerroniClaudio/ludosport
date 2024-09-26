<x-mail::message>

# {{ __('emails.event_participation_email_title', ['name' => $order->user->name]) }}

{{ __('emails.event_participation_email_introduction') }}

## {{ __('emails.event_participation_email_order_details') }}

-   **{{ __('emails.event_participation_email_order_number') }}**: {{ $order->order_number }}
-   **{{ __('emails.event_participation_email_order_date') }}**: {{ $order->created_at->format('d/m/Y') }}
-   **{{ __('emails.event_participation_email_total') }}**: {{ '€ ' . number_format($order->total, 2) . '' }}
-   **{{ __('emails.event_participation_email_payment_method') }}**: {{ __('orders.'.$order->payment_method) }}

## {{ __('emails.event_participation_email_event_details') }}

-   **{{ __('emails.event_participation_email_event_name')  }}**: {{ $event->name }}
-   **{{ __('emails.event_participation_email_event_date')  }}**: {{ $event->start_date->format('d/m/Y H:i') }}
-   **{{ __('emails.event_participation_email_event_location')  }}**: {{ $event->postal_code }}, {{ $event->address }}, {{ $event->city }}

{{ __('emails.fee_email_regards') }}
{{ config('app.name') }}

</x-mail::message>
