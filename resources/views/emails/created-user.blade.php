<x-mail::message>

# {{ __('emails.created_user_email_title', ['name' => $user->name . ' ' . $user->surname]) }}

{{ __('emails.created_user_email_introduction') }}

<x-mail::button url="{{ route('password.request') }}">
{{ __('emails.created_user_setup_password') }}
</x-mail::button>

</x-mail::message>
