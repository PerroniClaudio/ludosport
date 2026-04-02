import Chart from "chart.js/auto";
import {
    getUserMetricValue,
    sortUserMetricData,
} from "./dashboardUserMetrics.js";

export const userschoolgraphadmin = (role, academy, selectedAcademyData) => {
    return {
        academy: null,
        schoolData: [],
        filteredSchoolData: [],
        academyYearData: [],
        displayMode: "active",
        chart: null,
        currentSchoolsPage: 1,
        totalSchoolsPages: 1,
        paginatedSchools: [],
        colors: [
            "rgb(237,116,0)",
            "rgb(212, 145, 255)",
            "rgb(179,4,16)",
            "rgb(0,94,152)",
            "rgb(0,129,57)",
        ],
        async getAcademyYearData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/academies/${academy.id}/athletes-year-data`
            );
            const data = await response.json();

            return data;
        },
        getMetricValue(item) {
            return getUserMetricValue(item, this.displayMode);
        },
        getSortedSchoolData(items = this.schoolData) {
            return sortUserMetricData(items, this.displayMode);
        },
        setDisplayMode(mode) {
            if (!["active", "registered"].includes(mode)) {
                return;
            }

            this.displayMode = mode;
            this.refreshGraph();
            this.updateSchools();
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

        searchSchoolByValue(e) {
            const searchValue = e.target.value.toLowerCase();
            if (searchValue != '') {
                this.filteredSchoolData = this.schoolData.filter(row => {
                    return row.name.toLowerCase().includes(searchValue);
                });
                this.updateSchools();
            } else {
                this.filteredSchoolData = this.schoolData;
                this.updateSchools();
            }
        },
        
        
        nextPage() {
            if (this.currentSchoolsPage < this.totalSchoolsPages) {
                this.currentSchoolsPage++;
                this.updateSchools();
            }
        },
        previousPage() {
            if (this.currentSchoolsPage > 1) {
                this.currentSchoolsPage--;
                this.updateSchools();
            }
        },
        updateSchools() {
            const offset = (this.currentSchoolsPage - 1) * 10; // Assuming 10 items per page
            this.totalSchoolsPages = Math.ceil(this.filteredSchoolData.length / 10);
            const sortedSchoolData = this.getSortedSchoolData(this.filteredSchoolData);
            this.paginatedSchools = sortedSchoolData.slice(offset, offset + 10);
        },
        
        async init() {
            console.log("userschoolgraph initialized");
            this.academy = academy;
            console.log(academy.name);
            this.schoolData = selectedAcademyData;
            this.academyYearData = await this.getAcademyYearData();
            this.createGraph();
            this.filteredSchoolData = this.schoolData;
            this.totalSchoolsPages = Math.ceil(this.filteredSchoolData.length / 10);
            this.updateSchools();

            // Mando i dati al genitore, per evitare nuovi fetch inutili
            this.$dispatch("userschoolgraph-data", this.schoolData);
        },
    };
};
