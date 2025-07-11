export const rankingschart = () => {
    const today = new Date();

    return {
        events: [],
        selectedEvent: 0,
        selectedEventData: {},
        athletesData: [],
        nationFilter: "",
        nation: [],
        eventName: "General Rankings",
        getEventsList: async function (nationId = null) {
            const url = `/website-rankings/events/list?date=${today.toISOString()}${
                nationId ? "&nation=" + nationId : ""
            }`;
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
                let rank = 1;
                Object.entries(data).forEach(([key, value]) => {
                    this.athletesData.push({
                        id: key,
                        name: value.user_name,
                        rank: rank++,
                        battle_name: value.user_battle_name,
                        academy: value.user_academy,
                        school: value.user_school,
                        school_slug: value.school_slug,
                        nation: value.nation,
                        war_points: value.total_war_points,
                        style_points: value.total_style_points,
                    });
                });
            }

            this.rows = this.athletesData;
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
                    let rank = 1;
                    Object.entries(data).forEach(([key, value]) => {
                        this.athletesData.push({
                            id: key,
                            name: value.user_name,
                            rank: rank++,
                            battle_name: value.user_battle_name,
                            academy: value.user_academy,
                            school: value.user_school,
                            school_slug: value.school_slug,
                            nation: value.nation,
                            war_points: value.total_war_points,
                            style_points: value.total_style_points,
                        });
                    });
                }

                this.rows = this.athletesData;
            }
        },
        resetToGeneralRankings: function () {
            this.nationFilter = "";
            this.getGeneralRankings();
            this.getEventsList();
        },
        fiterByNation: async function (nationId) {
            if (nationId == "") {
                this.getGeneralRankings();
                this.getEventsList();
                return;
            }

            this.getEventsList(nationId);
            this.events = this.events.filter((a) => a.nation_id == nationId);
            this.athletesData = [];

            const res = await fetch(
                `/website-rankings/nation/${nationId}/rankings`
            );

            const data = await res.json();
            let rank = 1;

            Object.entries(data.results).forEach(([key, value]) => {
                this.athletesData.push({
                    id: key,
                    name: value.user_name,
                    rank: rank++,
                    battle_name: value.user_battle_name,
                    academy: value.user_academy,
                    school: value.user_school,
                    school_slug: value.school_slug,
                    nation: value.nation,
                    war_points: value.total_war_points,
                    style_points: value.total_style_points,
                });
            });

            this.eventName = "National Rankings - " + data.nation.name;
            this.nation = data.nation;
            this.rows = this.athletesData;
            this.selectedEvent = 0;
        },

        /** Tabella */

        columns: [
            {
                name: "Rank",
                field: "rank",
                columnClasses: "",
            },
            {
                name: "Name",
                field: "name",
                columnClasses: "",
            },
            {
                name: "Academy",
                field: "academy",
                columnClasses: "",
            },
            {
                name: "School",
                field: "school",
                columnClasses: "",
            },
            {
                name: "Nation",
                field: "nation",
                columnClasses: "",
            },
            {
                name: "Arena Points",
                field: "war_points",
                columnClasses: "",
            },
            {
                name: "Style Points",
                field: "style_points",
                columnClasses: "",
            },
        ],
        sortColumn: null,
        sortDirection: "asc",
        rows: [],
        sort: function (columnIndex) {
            if (this.sortColumn === columnIndex) {
                this.sortDirection =
                    this.sortDirection === "asc" ? "desc" : "asc";
            } else {
                this.sortColumn = columnIndex;
                this.sortDirection = "asc";
            }

            this.rows = [...this.rows].sort((a, b) => {
                const column = this.columns[columnIndex];
                const aValue = String(a[column.field]);
                const bValue = String(b[column.field]);
                if (!isNaN(aValue) && !isNaN(bValue)) {
                    return this.sortDirection === "asc"
                        ? aValue - bValue
                        : bValue - aValue;
                } else {
                    return this.sortDirection === "asc"
                        ? String(aValue).localeCompare(String(bValue))
                        : String(bValue).localeCompare(String(aValue));
                }

            });
        },
        searchByValue: function (e) {
            const search = e.target.value.toLowerCase();
            if (search === "") {
                this.rows = this.athletesData;
            } else {
                this.rows = this.athletesData.filter((row) => {
                    return Object.values(row).some((value) => {
                        return String(value).toLowerCase().includes(search);
                    });
                });
            }
        },
        page: 1,
        pageLength: 10,
        totalPages: function () {
            return Math.ceil(this.rows.length / this.pageLength);
        },
        paginatedRows: function () {
            const start = (this.page - 1) * this.pageLength;
            const end = start + this.pageLength;
            return this.rows.slice(start, end);
        },

        init() {
            this.getGeneralRankings();
            this.getEventsList();
        },
    };
};
