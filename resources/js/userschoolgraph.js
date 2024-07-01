import Chart from "chart.js/auto";

export const userschoolgraph = (academyId) => {
    return {
        academyId: academyId,
        schoolData: [],
        yearData: [],
        colors: [
            "rgb(237,116,0)",
            "rgb(212, 145, 255)",
            "rgb(179,4,16)",
            "rgb(0,94,152)",
            "rgb(0,129,57)",
        ],
        async getSchoolData() {
            const response = await fetch(
                `/academies/${this.academyId}/athletes-school-data`
            );
            const data = await response.json();

            return data;
        },
        async getYearData() {
            const response = await fetch(
                `/academies/${this.academyId}/athletes-year-data`
            );
            const data = await response.json();

            return data;
        },
        createGraph() {
            const ctx = document
                .getElementById("userschoolgraph")
                .getContext("2d");

            const labels = this.schoolData.map((school) => school.name);
            const dataCount = this.schoolData.map((school) => school.athletes);

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
            console.log("userschoolgraph initialized");
            this.schoolData = await this.getSchoolData();
            this.yearData = await this.getYearData();
            this.createGraph();
        },
    };
};
