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

            @if (!$event->is_approved)
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <p class="text-background-800 dark:text-background-200 text-xl">{{ __('events.approvation_text') }}
                    </p>
                    <div class="flex justify-end">
                        <a href="{{ route('events.review', $event->id) }}">
                            <x-primary-button role="button">
                                {{ __('events.review') }}
                            </x-primary-button>
                        </a>
                    </div>
                </div>
            @endif

            @if ($event->is_approved)
                @if (!$event->is_published)
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <p class="text-background-800 dark:text-background-200 text-xl">
                            {{ __('events.publishing_text') }}
                        </p>
                        <div class="flex justify-end">
                            <form method="POST" action="{{ route('events.publish', $event->id) }}">
                                @csrf
                                <x-primary-button type="submit">
                                    {{ __('events.publish') }}
                                </x-primary-button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <p class="text-background-800 dark:text-background-200 text-xl">
                            {{ __('events.published_text') }}
                        </p>
                    </div>
                @endif
            @endif

            <form method="POST" action={{ route('events.update', $event->id) }}
                class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                @csrf
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.info') }}</h3>
                    <x-primary-button type="sumbit">
                        <x-lucide-save class="w-5 h-5 text-white" />
                    </x-primary-button>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="flex flex-col gap-2 w-1/2">
                    <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                        value="{!! $event->name !!}" placeholder="{{ fake()->company() }}" />

                    <x-form.select name="event_type" label="Event Type" required="{{ true }}"
                        :options="$event->eventTypes()" value="{{ $event->event_type }}" />

                    <x-form.input name="start_date" label="Start Date" type="datetime-local"
                        required="{{ true }}" value="{{ $event->start_date }}"
                        placeholder="{{ fake()->date() }}" />

                    <x-form.input name="end_date" label="End Date" type="datetime-local" required="{{ true }}"
                        value="{{ $event->end_date }}" placeholder="{{ fake()->date() }}" />

                    <x-form.checkbox id="is_free" name="is_free" label="Free Event"
                        isChecked="{{ $event->is_free }}" />

                    <x-form.input name="price" label="Price (include taxes)" type="number"
                        value="{{ number_format($event->price, 2) }}"
                        required="{{ $event->is_free ? false : true }}" />

                </div>
            </form>

            <x-event.editor label="{{ __('events.promo') }}" value="{{ $event->description }}" :event="$event" />

            <x-event.map :event="$event" />

            <x-event.thumbnail :event="$event" />

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
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
