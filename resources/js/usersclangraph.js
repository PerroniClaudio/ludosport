import Chart from "chart.js/auto";
import {
    getUserMetricValue,
    sortUserMetricData,
} from "./dashboardUserMetrics.js";

export const usersclangraph = (schoolId, role) => {
    return {
        schoolId: schoolId,
        clanData: [],
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
        async getclanData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/schools/${this.schoolId}/athletes-clan-data`
            );
            const data = await response.json();

            return data;
        },
        async getYearData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/schools/${this.schoolId}/athletes-year-data`
            );
            const data = await response.json();

            return data;
        },
        getMetricValue(item) {
            return getUserMetricValue(item, this.displayMode);
        },
        getSortedClanData() {
            return sortUserMetricData(this.clanData, this.displayMode);
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
                .getElementById("usersclangraph")
                .getContext("2d");

            const sortedClanData = this.getSortedClanData();
            const labels = sortedClanData.map((clan) => clan.name);
            const dataCount = sortedClanData.map((clan) =>
                this.getMetricValue(clan)
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

            const sortedClanData = this.getSortedClanData();
            this.chart.data.labels = sortedClanData.map((clan) => clan.name);
            this.chart.data.datasets[0].data = sortedClanData.map((clan) =>
                this.getMetricValue(clan)
            );
            this.chart.update();
        },

        async init() {
            console.log("usersclangraph initialized");
            this.clanData = await this.getclanData();
            this.yearData = await this.getYearData();
            this.createGraph();
        },
    };
};
