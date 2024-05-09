<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('Edit User') }}
            </h2>

        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 w-full flex flex-col gap-2">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.personal_details_message') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-form.input  name="name" label="Name" type="text" required="{{ true }}" value="{{$user->name }}" placeholder="{{ fake()->firstName() }}"/>
                    <x-form.input  name="surname" label="Surname" type="text" required="{{ true }}" value="{{ $user->surname }}" placeholder="{{ fake()->lastName() }}"/>
                    <x-form.input  name="email" label="Email" type="email" required="{{ true }}" value="{{ $user->email }}" placeholder="{{ fake()->email() }}"/>
                    <x-form.input  name="year" label="Year" type="text" required="{{ true }}" value="{{ $user->subscription_year }}" placeholder="{{ date('Y') }}"/>
                </div>
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 w-full flex flex-col gap-2">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.provenance') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <x-user.provenance-selector />
          

                </div>
            </div>
        </div>
    </div>
</x-app-layout>