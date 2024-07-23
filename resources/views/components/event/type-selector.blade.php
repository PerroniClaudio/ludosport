@props([
    'types' => [],
    'selected' => null,
    'event_id' => null,
])

@php
    $authRole = auth()->user()->getRole();
    $addToRoute = $authRole === 'admin' ? '' : '/' . $authRole;
    // Li ho divisi così se cambiano i permessi si possono gestire in modo più semplice
    $canCreateType = in_array($authRole, ['admin']);
    $canSelectType = in_array($authRole, ['admin']);
@endphp

<div x-data="{
    newEventTypeName: '',
    event_id: {{ $event_id }},
    type_id: {{ $selected }},
    eventTypes: [],
    fetchEventTypes: async function() {

        const response = await fetch(`${'{{$addToRoute}}'}/event-types/json`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })

        if (response.ok) {
            const data = await response.json()
            console.log(data)
            this.eventTypes = data.eventTypes
        }
    },
    createNewEventType: async function() {

        const body = new FormData()
        body.append('name', this.newEventTypeName)
        body.append('event_id', this.event_id)


        const response = await fetch(`${'{{$addToRoute}}'}/event-types/create`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: body
        })

        if (response.ok) {
            $dispatch('close-modal', 'new-type-modal')
            this.fetchEventTypes()
            console.log('success')
        }

    },
    init: function() {
        this.fetchEventTypes()
    }
}">
    <x-input-label for="event_type" value="Event Type" />
    <div class="flex items-center gap-2">
        @if ($canCreateType)
            <a x-on:click.prevent="$dispatch('open-modal', 'new-type-modal')">
                <x-lucide-plus-circle class="w-5 h-5 text-primary-500 dark:text-primary-600" />
            </a>
        @endif
        <select name="event_type" id="event_type" x-model="type_id" 
            {{ $canSelectType ? '' : 'disabled' }}
            class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">

            <template x-for="type in eventTypes" :key="type.id">
                <option x-text="type.name" :selected="type.id == type_id" :value="type.id"></option>
            </template>

        </select>
    </div>
    @if ($canCreateType)
        <x-modal name="new-type-modal" :show="$errors->userId->isNotEmpty()" focusable>

            <div class="p-6 flex flex-col gap-2">

                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('events.types_create') }}
                </h2>

                <input type="hidden" name="event_id" value="{{ $event_id }}">


                <div>
                    <x-input-label value="Name" />
                    <input x-model="newEventTypeName" type="text" placeholder="Event Type Name"
                        class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>


                <div class="flex justify-end">
                    <x-primary-button type="button" @click="createNewEventType">
                        <span>{{ __('events.types_create') }}</span>
                    </x-primary-button>
                </div>
            </div>

        </x-modal>
    @endif
</div>
