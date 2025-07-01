<x-guest-layout>

    <div class="mb-8">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">
            {{ __('navigation.register_message') }}
        </h3>
        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

        <a href="{{ route('register') }}">
            <x-primary-button class="w-full">
                {{ __('navigation.register') }}
            </x-primary-button>
        </a>
    </div>

    <div>

        <h3 class="text-background-800 dark:text-background-200 text-2xl">
            {{ __('navigation.access_message') }}
        </h3>
        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

        <a href="{{ route('login') }}">
            <x-primary-button class="w-full">
                {{ __('navigation.login') }}
            </x-primary-button>
        </a>

    </div>


</x-guest-layout>
