<x-mail::message>

# {{ __('emails.event_waiting_list_add_email_title', ['name' => $user->name]) }}

{{ __('emails.event_waiting_list_add_email_introduction', ['event' => $event->name]) }}

@if (!$event->isFree() && $event->price > 0)
{{ __('emails.event_waiting_list_add_email_future_payment') }}
-   **{{ __('emails.event_waiting_list_add_email_total') }}**: {{ '€ ' . number_format($event->price, 2) . '' }}
@endif

## {{ __('emails.event_waiting_list_add_email_event_details') }}

-   **{{ __('emails.event_waiting_list_add_email_event_name')  }}**: {{ $event->name }}
-   **{{ __('emails.event_waiting_list_add_email_event_date')  }}**: {{ optional($event->start_date)->format('d/m/Y H:i') }}
-   **{{ __('emails.event_waiting_list_add_email_event_location')  }}**: {{ $event->postal_code }}, {{ $event->address }}, {{ $event->city }}

{{ __('emails.fee_email_regards') }}
{{ config('app.name') }}

</x-mail::message>
