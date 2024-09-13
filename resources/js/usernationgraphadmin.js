import Chart from "chart.js/auto";

export const usernationgraphadmin = (role) => {
    return {
        nationData: [],
        filteredNationData: [],
        worldYearData: [],
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
        createGraph() {
            const ctx = document
                .getElementById("usernationgraph")
                .getContext("2d");

            const labels = this.nationData.map((nation) => nation.name);
            const dataCount = this.nationData.map((nation) => nation.athletes);

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
            this.paginatedNations = this.filteredNationData.slice(offset, offset + 10);
        },
        
        async init() {
            console.log("usernationgraph initialized");
            this.nationData = await this.getNationData();
            this.worldYearData = await this.getWorldYearData();
            this.createGraph();
            this.filteredNationData = this.nationData;
            this.totalNationsPages = Math.ceil(this.filteredNationData.length / 10);
            this.updateNations();

            // Mando i dati al genitore, per evitare nuovi fetch inutili
            this.$dispatch("usernationgraph-data", this.nationData);
        },
    };
};
