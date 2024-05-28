<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <p>Welcome {{ auth()->user()->name }}!</p>
                    <p>You're logged in with the <span
                            class="text-primary-600">{{ __('users.' . auth()->user()->getRole()) }}</span> authorization!
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
