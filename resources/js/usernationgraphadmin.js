import Chart from "chart.js/auto";
import {
    getUserMetricValue,
    sortUserMetricData,
} from "./dashboardUserMetrics.js";

export const usernationgraphadmin = (role) => {
    return {
        nationData: [],
        filteredNationData: [],
        worldYearData: [],
        displayMode: "active",
        chart: null,
        currentNationsPage: 1,
        totalNationsPages: 1,
        paginatedNations: [],
        colors: [
            "rgb(237,116,0)",
            "rgb(212, 145, 255)",
            "rgb(179,4,16)",
            "rgb(0,94,152)",
            "rgb(0,129,57)",
        ],
        async getNationData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/world-athletes-data-list`
            );
            const data = await response.json();

            return data;
        },
        async getWorldYearData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/world-athletes-year-data`
            );
            const data = await response.json();

            return data;
        },
        getMetricValue(item) {
            return getUserMetricValue(item, this.displayMode);
        },
        getSortedNationData(items = this.nationData) {
            return sortUserMetricData(items, this.displayMode);
        },
        setDisplayMode(mode) {
            if (!["active", "registered"].includes(mode)) {
                return;
            }

            this.displayMode = mode;
            this.refreshGraph();
            this.updateNations();
        },
        createGraph() {
            const ctx = document
                .getElementById("usernationgraph")
                .getContext("2d");

            const sortedNationData = this.getSortedNationData();
            const labels = sortedNationData.map((nation) => nation.name);
            const dataCount = sortedNationData.map((nation) =>
                this.getMetricValue(nation)
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

            const sortedNationData = this.getSortedNationData();
            this.chart.data.labels = sortedNationData.map((nation) => nation.name);
            this.chart.data.datasets[0].data = sortedNationData.map((nation) =>
                this.getMetricValue(nation)
            );
            this.chart.update();
        },

        searchNationByValue(e) {
            const searchValue = e.target.value.toLowerCase();
            if (searchValue != '') {
                this.filteredNationData = this.nationData.filter(row => {
                    return row.name.toLowerCase().includes(searchValue);
                });
                this.updateNations();
            } else {
                this.filteredNationData = this.nationData;
                this.updateNations();
            }
        },
        
        
        nextPage() {
            if (this.currentNationsPage < this.totalNationsPages) {
                this.currentNationsPage++;
                this.updateNations();
            }
        },
        previousPage() {
            if (this.currentNationsPage > 1) {
                this.currentNationsPage--;
                this.updateNations();
            }
        },
        updateNations() {
            const offset = (this.currentNationsPage - 1) * 10; // Assuming 10 items per page
            this.totalNationsPages = Math.ceil(this.filteredNationData.length / 10);
            const sortedNationData = this.getSortedNationData(this.filteredNationData);
            this.paginatedNations = sortedNationData.slice(offset, offset + 10);
        },
        
        async init() {
            console.log("usernationgraph initialized");
            this.nationData = await this.getNationData();
            this.filteredNationData = this.nationData;
            this.totalNationsPages = Math.ceil(this.filteredNationData.length / 10);
            this.updateNations();
            this.worldYearData = await this.getWorldYearData();
            this.createGraph();

            // Mando i dati al genitore, per evitare nuovi fetch inutili
            this.$dispatch("usernationgraph-data", this.nationData);
        },
    };
};
