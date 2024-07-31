export const rankingschart = () => {
    const today = new Date();

    return {
        events: [],
        selectedEvent: 0,
        selectedEventData: {},
        athletesData: [],
        xdd: [],
        eventName: "General Rankings",
        getEventsList: async function () {
            const url = `/website-rankings/events/list?date=${today.toISOString()}`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                this.events = data;
            }
        },
        getDataForEvent: async function (id) {
            this.selectedEvent = id;
            this.xdd = [];
            const url = `/website-rankings/events/${this.selectedEvent}/rankings`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();

                Object.entries(data).forEach(([key, value]) => {
                    this.xdd.push({
                        id: key,
                        name: value.user_name,
                        war_points: value.total_war_points,
                        style_points: value.total_style_points,
                    });
                });
            }
        },
        getGeneralRankings: async function () {
            this.selectedEvent = 0;
            this.xdd = [];
            const url = `/website-rankings/general?date=${today.toISOString()}`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();

                Object.entries(data).forEach(([key, value]) => {
                    this.xdd.push({
                        id: key,
                        name: value.user_name,
                        war_points: value.total_war_points,
                        style_points: value.total_style_points,
                    });
                });
            }
        },
        init() {
            this.getGeneralRankings();
            this.getEventsList();
        },
    };
};
