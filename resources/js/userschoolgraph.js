import Chart from "chart.js/auto";
import {
    getUserMetricValue,
    sortUserMetricData,
} from "./dashboardUserMetrics.js";

export const userschoolgraph = (academyId, role) => {
    return {
        academyId: academyId,
        schoolData: [],
        yearData: [],
        displayMode: "active",
        chart: null,
        colors: [
            "rgb(237,116,0)",
            "rgb(212, 145, 255)",
            "rgb(179,4,16)",
            "rgb(0,94,152)",
            "rgb(0,129,57)",
        ],
        async getSchoolData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/academies/${this.academyId}/athletes-school-data`
            );
            const data = await response.json();

            return data;
        },
        async getYearData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/academies/${this.academyId}/athletes-year-data`
            );
            const data = await response.json();

            return data;
        },
        getMetricValue(item) {
            return getUserMetricValue(item, this.displayMode);
        },
        getSortedSchoolData() {
            return sortUserMetricData(this.schoolData, this.displayMode);
        },
        setDisplayMode(mode) {
            if (!["active", "registered"].includes(mode)) {
                return;
            }

            this.displayMode = mode;
            this.refreshGraph();
        },
        createGraph() {
            const ctx = document
                .getElementById("userschoolgraph")
                .getContext("2d");

            const sortedSchoolData = this.getSortedSchoolData();
            const labels = sortedSchoolData.map((school) => school.name);
            const dataCount = sortedSchoolData.map((school) =>
                this.getMetricValue(school)
            );

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

            this.chart = new Chart(ctx, config);
        },
        refreshGraph() {
            if (!this.chart) {
                this.createGraph();
                return;
            }

            const sortedSchoolData = this.getSortedSchoolData();
            this.chart.data.labels = sortedSchoolData.map((school) => school.name);
            this.chart.data.datasets[0].data = sortedSchoolData.map((school) =>
                this.getMetricValue(school)
            );
            this.chart.update();
        },

        async init() {
            console.log("userschoolgraph initialized");
            this.schoolData = await this.getSchoolData();
            this.yearData = await this.getYearData();
            this.createGraph();
        },
    };
};
