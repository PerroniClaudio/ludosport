<x-mail::message>

# {{ __('emails.event_must_pay_email_title', ['name' => $listItem->user->name]) }}

{{ __('emails.event_must_pay_email_introduction', ['event' => $event->name]) }}

{{ __('emails.event_must_pay_email_future_payment') }}

## {{ __('emails.event_must_pay_email_details') }}

-   **{{ __('emails.event_must_pay_email_total') }}**: {{ '€ ' . number_format($event->price, 2) . '' }}
-   **{{ __('emails.event_must_pay_email_deadline') }}**: {{ optional($listItem->payment_deadline)->format('d/m/Y') ?? 'Unspecified' }}

<x-mail::button url="{{ route('event-purchase', $event->id) }}" >
  {{ __('emails.event_must_pay_email_pay_now') }}
</x-mail::button>

## {{ __('emails.event_must_pay_email_event_details') }}

-   **{{ __('emails.event_must_pay_email_event_name')  }}**: {{ $event->name }}
-   **{{ __('emails.event_must_pay_email_event_date')  }}**: {{ optional($event->start_date)->format('d/m/Y H:i') }}
-   **{{ __('emails.event_must_pay_email_event_location')  }}**: {{ $event->postal_code }}, {{ $event->address }}, {{ $event->city }}

{{ __('emails.event_must_pay_email_regards') }}
{{ config('app.name') }}

</x-mail::message>