<x-app-layout>


    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('event.new') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form method="POST" :action="route('technician.events.store')">
                    @csrf
                    <div class="flex flex-col gap-2 w-1/2">
                        <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                            value="{{ old('name') }}" placeholder="{{ fake()->company() }}" />

                        <x-form.input name="date" label="Date" type="date" required="{{ true }}"
                            value="{{ old('date') }}" />

                        <div class="flex items-center">
                            <div class="flex-1">
                                <x-form.input name="start" label="Start" type="time"
                                    required="{{ true }}" value="{{ old('start') }}" />
                            </div>

                            <div class="flex-1">
                                <x-form.input name="end" label="End" type="time"
                                    required="{{ true }}" value="{{ old('end') }}" />
                            </div>
                        </div>

                        <x-form.input name="address" label="Location (Address)" type="text"
                            required="{{ true }}" value="{{ old('address') }}"
                            placeholder="{{ fake()->address() }}" />

                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <x-primary-button type="submit">
                            {{ __('events.create') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</x-app-layout>
