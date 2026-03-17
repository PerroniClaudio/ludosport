<x-mail::message>

# {{ __('emails.minor_approval_denied_title', ['name' => $user->name . ' ' . $user->surname]) }}

{{ __('emails.minor_approval_denied_introduction') }}

<x-mail::panel>
{{ $reason }}
</x-mail::panel>

{{ __('emails.minor_approval_denied_upload_again') }}

<x-mail::button url="{{ route('dashboard') }}">
{{ __('emails.minor_approval_denied_button') }}
</x-mail::button>

{{ __('emails.minor_approval_denied_regards') }}

</x-mail::message>
