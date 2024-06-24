@props(['events' => [], 'event_type' => null])


<div x-data="{ selectedEventId: null }">
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'events-modal')">
        <span>{{ __('events.associate_event') }}</span>
    </x-primary-button>

    <x-modal name="events-modal" :show="$errors->userId->isNotEmpty()" focusable>

        <form method="post" action="{{ route('events.associate_event', $event_type) }}" class="p-6" x-ref="form">
            @csrf

            <input type="hidden" name="event_id" x-model="selectedEventId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('events.associate_event') }}
            </h2>

            <x-table striped="false" :columns="[
                [
                    'name' => 'Id',
                    'field' => 'id',
                    'columnClasses' => '',
                    'rowClasses' => '',
                ],
                [
                    'name' => 'Name',
                    'field' => 'name',
                    'columnClasses' => '',
                    'rowClasses' => '',
                ],
                [
                    'name' => 'Start Date',
                    'field' => 'start_date',
                    'columnClasses' => '',
                    'rowClasses' => '',
                ],
                [
                    'name' => 'End Date',
                    'field' => 'end_date',
                    'columnClasses' => '',
                    'rowClasses' => '',
                ],
            ]" :rows="$events">
                <x-slot name="tableActions">
                    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'events-modal')"
                        x-on:click="selectedEventId = row.id; $nextTick(() => { $refs.form.submit(); })">
                        <span>{{ __('nations.select') }}</span>
                    </x-primary-button>
                </x-slot>
            </x-table>

        </form>

    </x-modal>

</div>
