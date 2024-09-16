{{-- <div x-data="usereventgraphadmin('{{ $authRole }}')"> --}}
{{-- <div x-data="eventsparticipantsyeargraph('{{ $authUser }}')"> --}}
{{-- <div x-data="{
    eventList: [],
    filteredEventList: [],
    currentEventsPage: 1,
    totalEventsPages: 1,
    paginatedEvents: [],
    colors: [
        'rgb(237,116,0)',
        'rgb(212, 145, 255)',
        'rgb(179,4,16)',
        'rgb(0,94,152)',
        'rgb(0,129,57)',
    ],

    async getEventList() {
        const response = await fetch('/technician/dashboard-events');
        const data = await response.json();
        return data;
    },

    createGraph() {
        const ctx = document
            .getElementById('participantseventsgraph')
            .getContext('2d');

        const labels = this.eventList.map((event) => event.name);
        const dataCount = this.eventList.map((event) => event.athletes);

        const data = {
            labels: labels,
            datasets: [
                {
                    label: 'Athletes',
                    data: dataCount,
                    backgroundColor: this.colors,
                    hoverOffset: 4,
                },
            ],
        };

        const config = {
            type: 'pie',
            data: data,
        };

        const chart = new Chart(ctx, config);
    },

    searchEventByValue(event) {
        const searchValue = e.target.value.toLowerCase();
            if (searchValue != '') {
                this.filteredEventList = this.EventList.filter(row => {
                    return row.name.toLowerCase().includes(searchValue);
                });
                this.updateEvents();
            } else {
                this.filteredEventList = this.EventList;
                this.updateEvents();
            }
    },

    nextPage() {
        if (this.currentEventsPage < this.totalEventsPages) {
            this.currentEventsPage++;
            this.updateEvents();
        }
    },
    previousPage() {
        if (this.currentEventsPage > 1) {
            this.currentEventsPage--;
            this.updateEvents();
        }
    },
    updateEvents() {
        const offset = (this.currentEventsPage - 1) * 10; // Assuming 10 items per page
        this.totalEventsPages = Math.ceil(this.filteredEventList.length / 10);
        this.paginatedEvents = this.filteredEventList.slice(offset, offset + 10);
    },

    async init() {
        console.log('participantseventsgraph initialized');
        this.eventList = await this.getEventList();
        console.log(this.eventList);
        this.createGraph();
        this.filteredEventList = this.eventList;
        this.totalEventsPages = Math.ceil(this.filteredEventList.length / 10);
        this.updateEvents();
    },
}"> --}}
<div x-data="eventsparticipantsgraph()">

    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-background-900 dark:text-background-100">
            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                {{ __('dashboard.technician_events_participants_title') }}
            </h3>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <canvas id="participantseventsgraph"></canvas>
                </div>
                <div class="flex flex-col gap-8">
                    <div class="flex flex-col gap-4 grow">
                        <h3 class="text-background-800 dark:text-background-200 text-lg">
                            {{ __('dashboard.events_with_participants_char') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <x-text-input type="text" x-on:input="searchEventByValue(event)" placeholder="Search..."
                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                        <ul class="flex flex-col gap-2 px-0 grow">
                            <template x-for="event in paginatedEvents" :key="event.id">
                                <li class="flex items-center gap-4" >
                                    <div class="grow flex justify-between">
                                        <span x-text="event.name"></span>
                                        <span x-text="event.participants"></span>
                                    </div>
                                    <div class="flex gap-2">
                                        <x-primary-link-button-small x-bind:href="'/technician/events/' + event.id" >
                                            <x-lucide-pencil class="h-6 w-6 text-white" />
                                        </x-primary-link-button-small>
                                    </div>
                                </li>
                            </template>
                        </ul>
    
                        <div class="flex justify-between ">
                            <x-primary-button-small @click="previousPage" x-bind:disabled="currentEventsPage === 1">
                                <x-lucide-chevron-left class="h-6 w-6 text-white" />
                            </x-primary-button-small>
                            <span>Page <span x-text="currentEventsPage"></span> of <span x-text="totalEventsPages"></span></span>
                            <x-primary-button-small @click="nextPage" x-bind:disabled="currentEventsPage === totalEventsPages">
                                <x-lucide-chevron-right class="h-6 w-6 text-white" />
                            </x-primary-button-small>
                        </div>
    
                        
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
