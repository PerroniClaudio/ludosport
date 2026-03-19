<x-app-layout>
    <div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-background-800 shadow-sm rounded-lg p-6 sm:p-8">
            <h1 class="text-2xl font-semibold text-background-900 dark:text-background-100">
                Adult Account Email Confirmation
            </h1>

            <p class="mt-3 text-sm text-background-600 dark:text-background-400">
                You have reached the age of 18. Before unlocking the profile features that were restricted for minor accounts, you must confirm the email address used to access your account.
            </p>

            <p class="mt-2 text-sm text-background-600 dark:text-background-400">
                You can keep the same email address or replace it with a new one. After saving, we will send you a verification link.
            </p>

            <form method="POST" action="{{ route('minor-switch.update') }}" class="mt-6 space-y-6">
                @csrf
                @method('put')

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                        :value="old('email', $user->email)" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>
                        Send Verification Link
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
