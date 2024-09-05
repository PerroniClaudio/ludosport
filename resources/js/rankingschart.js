export const rankingschart = () => {
    const today = new Date();

    return {
        events: [],
        selectedEvent: 0,
        selectedEventData: {},
        athletesData: [],
        nationFilter: "",
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
            this.athletesData = [];
            const url = `/website-rankings/events/${this.selectedEvent}/rankings`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();

                Object.entries(data).forEach(([key, value]) => {
                    this.athletesData.push({
                        id: key,
                        name: value.user_name,
                        academy: value.user_academy,
                        school: value.user_school,
                        nation: value.nation,
                        war_points: value.total_war_points,
                        style_points: value.total_style_points,
                    });
                });
            }
        },
        getGeneralRankings: async function () {
            if (this.nationFilter != "") {
                this.fiterByNation(this.nationFilter);
            } else {
                this.selectedEvent = 0;
                this.athletesData = [];
                this.eventName = "General Rankings";
                const url = `/website-rankings/general?date=${today.toISOString()}`;
                const response = await fetch(url);

                if (response.ok) {
                    const data = await response.json();

                    Object.entries(data).forEach(([key, value]) => {
                        this.athletesData.push({
                            id: key,
                            name: value.user_name,
                            academy: value.user_academy,
                            school: value.user_school,
                            nation: value.nation,
                            war_points: value.total_war_points,
                            style_points: value.total_style_points,
                        });
                    });
                }
            }
        },
        fiterByNation: async function (nationId) {
            this.events = this.events.filter((a) => a.nation_id == nationId);
            this.athletesData = [];

            const res = await fetch(
                `/website-rankings/nation/${nationId}/rankings`
            );

            const data = await res.json();
            Object.entries(data.results).forEach(([key, value]) => {
                this.athletesData.push({
                    id: key,
                    name: value.user_name,
                    academy: value.user_academy,
                    school: value.user_school,
                    nation: value.nation,
                    war_points: value.total_war_points,
                    style_points: value.total_style_points,
                });
            });

            this.eventName = "General Rankings - " + data.nation.name;
        },
        init() {
            this.getGeneralRankings();
            this.getEventsList();
        },
    };
};
