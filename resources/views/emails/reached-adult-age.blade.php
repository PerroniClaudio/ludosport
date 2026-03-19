<x-mail::message>

# Adult Account Verification

Hello {{ $user->name }} {{ $user->surname }},

Our records show that you have now reached the age of 18.

Before we can unlock the profile features previously restricted for minor accounts, you need to log in and confirm the email address used for your account. You can keep the same email address or update it during the process.

<x-mail::button url="{{ route('login') }}">
Log in and continue
</x-mail::button>

After completing the email verification, your account will no longer be treated as a minor account and the related profile restrictions will be removed.

Thanks,<br>
{{ config('app.name') }}

</x-mail::message>
