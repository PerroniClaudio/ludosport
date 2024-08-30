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
            <form method="POST" action={{ route('technician.events.update', $event->id) }}
                class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                @csrf
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.info') }}</h3>
                    {{-- @if (!$event->is_approved)
                        <x-primary-button type="sumbit">
                            <x-lucide-save class="w-5 h-5 text-white" />
                        </x-primary-button>
                    @endif --}}
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="flex flex-col gap-2 w-1/2">
                    <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                        :value="$event->name" placeholder="{{ fake()->company() }}" 
                        disabled />
                        {{-- disabled="{{!!$event->is_approved}}" /> --}}

                    <x-form.input name="start_date" label="Start Date" type="datetime-local"
                        required="{{ true }}" value="{{ $event->start_date }}"
                        placeholder="{{ fake()->date() }}" 
                        disabled />
                        {{-- disabled="{{!!$event->is_approved}}" /> --}}

                    <x-form.input name="end_date" label="End Date" type="datetime-local" required="{{ true }}"
                        value="{{ $event->end_date }}" placeholder="{{ fake()->date() }}" 
                        disabled />
                        {{-- disabled="{{!!$event->is_approved}}" /> --}}

                </div>
            </form>

            <x-event.editor label="{{ __('events.promo') }}" value="{{ $event->description }}" :event="$event" />

            <x-event.map :event="$event" />

            <x-event.thumbnail :event="$event" />

            <x-event.personnel :event="$event" />

            @if ($event->is_approved)
                <x-event.ranking-participants :event="$event" />
            @endif
            
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.results') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'User',
                            'field' => 'user_fullname',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'War Points',
                            'field' => 'war_points',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td]
                        ],
                        [
                            'name' => 'Bonus',
                            'field' => 'bonus_war_points',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td]
                        ],
                        [
                            'name' => 'Style Points',
                            'field' => 'style_points',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td]
                        ],
                        [
                            'name' => 'Bonus',
                            'field' => 'bonus_style_points',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td]
                        ],
                        [
                            'name' => 'Total War Points',
                            'field' => 'total_war_points',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td]
                        ],
                        [
                            'name' => 'Total Style Points',
                            'field' => 'total_style_points',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td]
                        ],
                    ]" :rows="$results">

                    </x-table>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
