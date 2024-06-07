@props(['event' => ''])


<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-data="{}">
    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.participants') }}</h3>
        <div>
            <x-primary-button type="button">
                {{ __('events.export_participants') }}
            </x-primary-button>
        </div>
    </div>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
</div>
