import Chart from "chart.js/auto";

export const usersclangraph = (schoolId) => {
    return {
        schoolId: schoolId,
        clanData: [],
        yearData: [],
        colors: [
            "rgb(237,116,0)",
            "rgb(212, 145, 255)",
            "rgb(179,4,16)",
            "rgb(0,94,152)",
            "rgb(0,129,57)",
        ],
        async getclanData() {
            const response = await fetch(
                `/schools/${this.schoolId}/athletes-clan-data`
            );
            const data = await response.json();

            return data;
        },
        async getYearData() {
            const response = await fetch(
                `/schools/${this.schoolId}/athletes-year-data`
            );
            const data = await response.json();

            return data;
        },
        createGraph() {
            const ctx = document
                .getElementById("usersclangraph")
                .getContext("2d");

            const labels = this.clanData.map((clan) => clan.name);
            const dataCount = this.clanData.map((clan) => clan.athletes);

            const data = {
                labels: labels,
                datasets: [
                    {
                        label: "Athletes",
                        data: dataCount,
                        backgroundColor: this.colors,
                        hoverOffset: 4,
                    },
                ],
            };

            const config = {
                type: "pie",
                data: data,
            };

            const chart = new Chart(ctx, config);
        },

        async init() {
            console.log("usersclangraph initialized");
            this.clanData = await this.getclanData();
            this.yearData = await this.getYearData();
            this.createGraph();
        },
    };
};
