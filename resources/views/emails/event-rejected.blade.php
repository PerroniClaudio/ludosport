<x-mail::message>

# {{ __('events.rejected_event_title') }}

{{ __('events.reject_event_message') }}\
{{ __('events.reject_reason') }}:
<x-mail::panel>
{{ $reason }}
</x-mail::panel>
</x-mail::message>
