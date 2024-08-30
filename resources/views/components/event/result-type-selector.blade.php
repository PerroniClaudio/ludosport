@props([
    'types' => [],
    'selected_result_type' => null,
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
    event_id: {{ $event_id }},
    result_type: '{{ $selected_result_type }}',
    selected_result_type: '',
    eventResultTypes: [],
    fetchEventResultTypes: async function() {

        const response = await fetch(`${'{{ $addToRoute }}'}/event-result-types/json`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })

        if (response.ok) {
            const data = await response.json()
            console.log(data)
            this.eventResultTypes = data
            console.log(this.result_type)
            this.selected_result_type = this.eventResultTypes.find(type => type === this.result_type) || ''
        }
    },
    init: function() {
        this.fetchEventResultTypes()
    }
}">
    <x-input-label for="event_result_type_input" value="Event Type" />
    <div class="flex items-center gap-2">
        <select name="result_type" id="event_result_type_input" x-model="selected_result_type"
            class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">

            <template x-for="type in eventResultTypes" :key="type">
                <option x-text="type"></option>
            </template>

        </select>
    </div>
</div>
