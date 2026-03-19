<x-guest-layout>
    <div class="mb-4 text-sm text-background-600 dark:text-background-400">
        @if (auth()->user()?->has_to_switch_from_minor)
            {{ __('Before continuing, confirm the email address linked to your adult account by clicking the link we just emailed to you. If you did not receive it, we can send another one.') }}
        @else
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        @endif
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    @if (session('status') == 'adult-verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ __('A verification link has been sent to the email address you selected for your adult account.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit"
                class="underline text-sm text-background-600 dark:text-background-400 hover:text-background-900 dark:hover:text-background-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-background-800">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
