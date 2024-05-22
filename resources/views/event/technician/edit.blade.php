<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('events.edit') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.info') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="flex flex-col gap-2 w-1/2">
                    <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                        value="{{ $event->name }}" placeholder="{{ fake()->company() }}" />

                    <x-form.input name="start_date" label="Start Date" type="datetime-local"
                        required="{{ true }}" value="{{ $event->start_date }}"
                        placeholder="{{ fake()->date() }}" />

                    <x-form.input name="end_date" label="End Date" type="datetime-local" required="{{ true }}"
                        value="{{ $event->end_date }}" placeholder="{{ fake()->date() }}" />

                </div>
            </div>

            <x-event.editor label="{{ __('events.promo') }}" value="{{ $event->description }}" :event="$event" />


            <x-event.map :event="$event" />

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.thumbnail') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            </div>

        </div>
    </div>
</x-app-layout>
