import Chart from "chart.js/auto";

export const eventsparticipantsgraph = () => {
    return {
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
            // const dataCount = this.eventList.map((event) => {
            //     return event.result_type == 'enabling' ? event.instructor_results : event.results;
            // });
            const dataCount = this.eventList.map((event) => event.participants);
    
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
    
        searchEventByValue(e) {
            const searchValue = e.target.value.toLowerCase();
                if (searchValue != '') {
                    this.filteredEventList = this.eventList.filter(row => {
                        return row.name.toLowerCase().includes(searchValue);
                    });
                    this.updateEvents();
                } else {
                    this.filteredEventList = this.eventList;
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
    };
};
