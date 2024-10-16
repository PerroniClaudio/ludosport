<x-mail::message>

# {{ __('emails.event_waiting_list_remove_email_title', ['name' => $user->name]) }}

{{ __('emails.event_waiting_list_remove_email_introduction', ['event' => $event->name]) }}

{{ __('emails.event_waiting_list_remove_email_regards') }}
{{ config('app.name') }}

</x-mail::message>
