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
                @if ($event->nation_id != 0)
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <p class="text-background-800 dark:text-background-200 text-xl">
                            {{ __('events.approvation_text') }}
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
                    {{-- @if(!$event->is_approved) --}}
                        <x-primary-button type="sumbit">
                            <x-lucide-save class="w-5 h-5 text-white" />
                        </x-primary-button>
                    {{-- @endif --}}
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="flex flex-col gap-2 w-1/2">
                    
                    <x-form.input name="" label="Academy" type="text" required="{{ true }}"
                        :value="$event->academy->name" placeholder="" 
                        disabled="{{ true }}" />

                    <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                        :value="$event->name" placeholder="{{ fake()->company() }}" 
                        disabled="{{!!$event->is_approved}}" />

                    <x-form.input name="start_date" label="Start Date" type="datetime-local"
                        required="{{ true }}" value="{{ $event->start_date }}"
                        placeholder="{{ fake()->date() }}" 
                        disabled="{{!!$event->is_approved}}" />

                    <x-form.input name="end_date" label="End Date" type="datetime-local" required="{{ true }}"
                        value="{{ $event->end_date }}" placeholder="{{ fake()->date() }}" 
                        disabled="{{!!$event->is_approved}}" />

                    <x-event.type-selector event_id="{{ $event->id }}" :types="$event->eventTypes()"
                        selected="{{ $event->type->id }}" 
                        disabled="{{!!$event->is_approved}}" />

                    <x-form.input name="max_participants" label="Max Participants (0 means unlimited)" type="number" required="{{ true }}"
                        value="{{ $event->max_participants }}" min="{{0}}" 
                        placeholder="{{ __('events.max_participants_placeholder') }}"
                        disabled="{{!!$event->is_approved}}" />

                    <x-event.weapon-form event_id="{{ $event->id }}" :selected_weapon="$event->weaponForm" :available_weapons="$weaponForms" 
                        disabled="{{!!$event->is_approved}}" />

                    <x-form.checkbox id="block_subscriptions" name="block_subscriptions" label="Block subscriptions (shop)"
                        isChecked="{{ $event->block_subscriptions }}" 
                        disabled="{{false}}" />

                    <x-form.checkbox id="is_free" name="is_free" label="Free Event"
                        isChecked="{{ $event->is_free }}" 
                        disabled="{{!!$event->is_approved}}" />

                    <x-form.input name="price" label="Price (include taxes)" type="number"
                        value="{{ number_format($event->price, 2) }}"
                        min="{{0}}" 
                        required="{{ $event->is_free ? false : true }}" 
                        disabled="{{!!$event->is_approved}}" />

                </div>
            </form>

            <x-event.editor label="{{ __('events.promo') }}" value="{{ $event->description }}" :event="$event" />

            <x-event.map :event="$event" />

            <x-event.thumbnail :event="$event" />

            <x-event.personnel :event="$event" />
            
            @if ($event->is_approved)
                @if ($event->resultType() === 'enabling')
                    <x-event.enabling-participants :event="$event" />
                    <x-event.waiting-list :waiting_list="$waitingList" />
                    <x-event.enabling-results :event="$event" :results="$enablingResults" />
                @elseif ($event->resultType() === 'ranking')
                    <x-event.ranking-participants :event="$event" :results="$rankingResults" />
                    <x-event.waiting-list :event="$event" :waiting_list="$waitingList" />
                    <x-event.ranking-results :results="$rankingResults" />
                @endif
            @endif
            
            

        </div>
    </div>
</x-app-layout>
