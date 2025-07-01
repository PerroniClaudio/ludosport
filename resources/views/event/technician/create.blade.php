<x-app-layout>


    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('events.new') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">

                <form method="POST" action="{{ route('technician.events.store') }}">

                    @csrf
                    <div class="flex flex-col gap-2 w-1/2">
                        <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                            value="{{ old('name') }}" placeholder="{{ fake()->words(3, true) }}" />

                        <x-event.academies :academies="$academies" />

                        <x-form.input name="start_date" label="Start Date" type="datetime-local"
                            required="{{ true }}" value="{{ old('start_date') }}" />

                        <x-form.input name="end_date" label="End Date" type="datetime-local"
                            required="{{ true }}" value="{{ old('end_date') }}" />



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
