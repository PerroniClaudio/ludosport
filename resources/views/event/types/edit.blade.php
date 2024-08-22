<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('events.event_types_edit') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <form method="POST" action="{{ route('events.update_type', $eventType->id) }}"
                class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.type_info') }}</h3>
                    <x-primary-button type="sumbit">
                        <x-lucide-save class="w-5 h-5 text-white" />
                    </x-primary-button>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="flex flex-col gap-2 w-1/2">
                    @csrf
                    <x-form.input name="name" label="Name" :value="$eventType->name" required />
                </div>

            </form>


            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('events.type_associated_events') }}
                        </h3>
                        <x-event.associate-event-button event_type="{{ $eventType->id }}" :events="$events" />
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Name',
                            'field' => 'name',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Date',
                            'field' => 'start_date',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td]
                        ],
                    ]" :rows="$eventType->events">
                        <x-slot name="tableActions">
                            <a x-bind:href="'/events/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            </div>

            @if (!$eventType->is_disabled)
                <x-event.type-disable-form event_type="{{ $eventType->id }}" />
            @endif


        </div>
    </div>
</x-app-layout>
