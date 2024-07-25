export const rankingschart = () => {
    const today = new Date();

    return {
        events: [],
        selectedEvent: 0,
        selectedEventData: {},
        athletesData: [],
        getEventsList: async function () {
            const url = `/website-rankings/events/list?date=${today.toISOString()}`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                this.events = data;
            }
        },
        getDataForEvent: async function () {
            const url = `/website-rankings/events/${this.selectedEvent}/rankings`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                this.selectedEventData = data.event;
                this.athletesData = data.athletes;
            }
        },
        getGeneralRankings: async function () {
            const url = `/website-rankings/general?date=${today.toISOString()}`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                this.athletesData = data;
            }
        },
        init() {
            this.getGeneralRankings();
            this.getEventsList();
        },
    };
};
