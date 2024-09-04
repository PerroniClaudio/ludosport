@props(['type' => ''])

<form action="{{ route('exports.store') }}" method="POST" x-data="{
    filters: [],
    filtersJson: '[]',
    selectedEvents: [],
    availableEvents: [],
    paginatedEvents: [],
    paginatedselectedEvents: [],
    currentPage: 1,
    totalPages: 1,
    isSubmitEnabled: false,
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
    searchavailableEvents: function(event) {

        if (event.target.value.length >= 3) {
            fetch('/events/search?search=' + event.target.value)
                .then(response => response.json())
                .then(data => {
                    this.availableEvents = data;
                    this.paginatedEvents = this.availableEvents.slice(0, 10);
                    this.totalPages = Math.ceil(this.availableEvents.length / 10);
                })
                .catch(error => {
                    console.error(error);
                });
        } else {
            this.getavailableEvents();
        }
    },
    searchselectedEvents: function(event) {
        if (event.target.value.length >= 3) {
            this.paginatedselectedEvents = this.selectedEvents.filter(course => course.name.toLowerCase().includes(event.target.value.toLowerCase()));
        } else {
            this.paginatedselectedEvents = this.selectedEvents;
        }
    },
    addEvent: function(id) {
        if (this.selectedEvents.find(event => event.id === id)) {
            return;
        }
        let course = this.availableEvents.find(course => course.id === id);
        this.selectedEvents.push(course);
        this.paginatedselectedEvents = this.selectedEvents;
        this.updateFilterJson()
    },
    removeEvent: function(id) {
        this.selectedEvents = this.selectedEvents.filter(course => course.id !== id);
        this.paginatedselectedEvents = this.selectedEvents;
        this.updateFilterJson()
    },
    goToPage: function(page) {
        if (page < 1 || page > this.totalPages) {
            return;
        }

        this.currentPage = page;
        this.paginatedEvents = this.availableEvents.slice((page - 1) * 10, page * 10);
    },
    updateFilterJson: function() {
        this.filtersJson = JSON.stringify(this.selectedEvents);
        this.validateForm()
    },
    validateForm: function() {
        if (this.selectedEvents.length > 0) {
            this.isSubmitEnabled = true;
            return;
        }

        this.isSubmitEnabled = false;
    },
    formatDate: function(date) {
        return new Date(date).toLocaleDateString();
    },
    init: function() {
        this.getavailableEvents();

    }
}">
    @csrf

    <input name="type" type="hidden" value="{{ $type }}">
    <input name="filters" type="hidden" x-model="filtersJson">

    <div class="grid grid-cols-2 gap-2 my-4">
        <div class="bg-background-900 p-4 rounded">
            <div class="flex justify-between gap-2 items-center">
                <div class="flex-1">
                    <h4 class="text-background-800 dark:text-background-200 text-lg">
                        {{ __('exports.available_events') }}
                    </h4>
                </div>
                <div>
                    <x-text-input type="text" x-on:input="searchavailableEvents(event);" placeholder="Search..."
                        class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                </div>
            </div>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

            <table
                class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                <thead>
                    <tr class="">
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
                                <button type="button" @click="addEvent(row.id)">
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
                    <button type="button" x-on:click="goToPage(1)" class="mr-2" x-bind:disabled="currentPage === 1">
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

        <div class="bg-background-900 p-4 rounded">
            <div class="flex justify-between gap-2 items-center">
                <div class="flex-1">
                    <h4 class="text-background-800 dark:text-background-200 text-lg">
                        {{ __('exports.selected_events') }}
                    </h4>

                </div>

                <div>
                    <x-text-input type="text" x-on:input="searchavailableEvents(event);" placeholder="Search..."
                        class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                </div>

            </div>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

            <table
                class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                <thead>
                    <tr class="">
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
                    <template x-for="(row, index) in paginatedselectedEvents">
                        <tr>
                            <td class="text-background-500 dark:text-background-300 text-sm" x-text="row.name"></td>
                            <td class="text-background-500 dark:text-background-300 text-sm"
                                x-text="formatDate(row.start_date)">
                            </td>
                            <td class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                <button type="button" @click="removeEvent(row.id)">
                                    <x-lucide-minus
                                        class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>



    </div>

    <div class="flex justify-end w-full my-4">
        <button type="submit" :disabled="!isSubmitEnabled"
            class="inline-flex items-center px-4 py-2 bg-primary-800 dark:bg-primary-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-background-800 uppercase tracking-widest hover:bg-background-700 dark:hover:bg-primary-600 focus:bg-background-700 dark:focus:bg-primary-500 active:bg-background-900 dark:active:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150 disabled:cursor-not-allowed disabled:pointer-events-none disabled:opacity-60 ">
            {{ __('exports.submit') }}
        </button>
    </div>
</form>
