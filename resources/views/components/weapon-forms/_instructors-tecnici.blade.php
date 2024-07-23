@props([
    'weapon_form_id' => 0,
])

<div x-data="{
    selectedEvent: 0,
    currentPage: 1,
    totalPages: 1,
    paginatedEvents: [],
    events: [],
    availableEvents: [],
    openFromEventModal() {
        this.closeMainModal();
        $dispatch('open-modal', 'event-modal');
    },
    openHandpickModal() {
        this.closeMainModal();
        $dispatch('open-modal', 'handpick-modal');
    },
    closeMainModal() {
        $dispatch('close-modal', 'select-type-modal');
    },
    getavailableEvents: function() {
        fetch('/events/all')
            .then(response => response.json())
            .then(data => {
                this.availableEvents = data;
                this.paginatedEvents = this.availableEvents.slice(0, 10);
                this.totalPages = Math.ceil(this.availableEvents.length / 10);
            })
            .catch(error => {
                console.error(error);
            });
    },
    selectEvent: function(eventId) {
        this.selectedEvent = eventId;
        $dispatch('close-modal', 'event-modal');
    },
    formatDate: function(date) {
        return new Date(date).toLocaleDateString();
    },

    init: function() {
        this.getavailableEvents();

    }
}">

    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'select-type-modal')">
        <x-lucide-plus class="w-6 h-6 text-white" />
    </x-primary-button>

    <x-modal name="select-type-modal" :show="$errors->userId->isNotEmpty()">
        <div class="p-6">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('weaponf.associate_instructors') }}
            </h2>

            <p>{{ __('weaponf.associate_instructors_modal_text') }}</p>

            <div class="grid grid-cols-2 gap-4 my-4">
                <x-primary-button @click="openFromEventModal">
                    <span>{{ __('weaponf.associate_from_event') }}</span>
                </x-primary-button>
                <x-primary-button @click="openHandpickModal">
                    <span>{{ __('weaponf.associate_handpick') }}</span>
                </x-primary-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="event-modal" :show="$errors->userId->isNotEmpty()">
        <div class="p-6">
            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('weaponf.associate_from_event') }}
            </h2>

            <p>
                {{ __('weaponf.associate_from_event_modal_text') }}
            </p>

            <div class="bg-background-900 p-4 rounded">
                <table
                    class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                    <thead>
                        <tr>
                            <th
                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ __('events.title') }}</th>
                            <th
                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ __('events.start_date') }}</th>
                            <th
                                class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                {{ __('users.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in paginatedEvents">
                            <tr>
                                <td class="text-background-500 dark:text-background-300 text-sm" x-text="row.name"></td>
                                <td class="text-background-500 dark:text-background-300 text-sm"
                                    x-text="formatDate(row.start_date)"></td>
                                </td>
                                <td class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                    <button type="button" @click="selectEvent(row.id)">
                                        <x-lucide-plus
                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="flex items-center">
                    <div class="flex-1">

                    </div>
                    <div class="flex justify-between items-center">
                        <button type="button" x-on:click="goToPage(1)" class="mr-2"
                            x-bind:disabled="currentPage === 1">
                            <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        </button>
                        <button type="button" x-on:click="goToPage(currentPage - 1)" class="mr-2"
                            x-bind:disabled="currentPage === 1"
                            :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }">
                            <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        </button>
                        <p class="text-sm text-background-500 dark:text-background-300">Page <span
                                x-text="currentPage"></span>
                            of
                            <span x-text="totalPages"></span>
                        </p>
                        <button type="button" x-on:click="goToPage(currentPage + 1)" class="ml-2"
                            x-bind:disabled="currentPage === totalPages"
                            :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }">
                            <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        </button>
                        <button type="button" x-on:click="goToPage(totalPages)" class="ml-2"
                            x-bind:disabled="currentPage === totalPages">
                            <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-modal>
    <x-modal name="handpick-modal" :show="$errors->userId->isNotEmpty()" focusable>
        <div class="p-6">

        </div>
    </x-modal>


</div>
