import Chart from "chart.js/auto";

export const useracademygraphadmin = (role, nation, selectedNationData) => {
    return {
        nation: null,
        academyData: [],
        filteredAcademyData: [],
        nationYearData: [],
        currentAcademiesPage: 1,
        totalAcademiesPages: 1,
        paginatedAcademies: [],
        colors: [
            "rgb(237,116,0)",
            "rgb(212, 145, 255)",
            "rgb(179,4,16)",
            "rgb(0,94,152)",
            "rgb(0,129,57)",
        ],
        async getNationYearData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/nations/${nation.id}/athletes-year-data`
            );
            const data = await response.json();

            return data;
        },
        createGraph() {
            const ctx = document
                .getElementById("useracademygraph")
                .getContext("2d");

            const labels = this.academyData.map((academy) => academy.name);
            const dataCount = this.academyData.map((academy) => academy.athletes);

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

        searchAcademyByValue(e) {
            const searchValue = e.target.value.toLowerCase();
            if (searchValue != '') {
                this.filteredAcademyData = this.academyData.filter(row => {
                    return row.name.toLowerCase().includes(searchValue);
                });
                this.updateAcademies();
            } else {
                this.filteredAcademyData = this.academyData;
                this.updateAcademies();
            }
        },
        
        
        nextPage() {
            if (this.currentAcademiesPage < this.totalAcademiesPages) {
                this.currentAcademiesPage++;
                this.updateAcademies();
            }
        },
        previousPage() {
            if (this.currentAcademiesPage > 1) {
                this.currentAcademiesPage--;
                this.updateAcademies();
            }
        },
        updateAcademies() {
            const offset = (this.currentAcademiesPage - 1) * 10; // Assuming 10 items per page
            // this.paginatedAcademies = this.academyData.slice(offset, offset + 10);

            this.totalAcademiesPages = Math.ceil(this.filteredAcademyData.length / 10);
            this.paginatedAcademies = this.filteredAcademyData.slice(offset, offset + 10);
        },
        
        async init() {
            console.log("useracademygraph initialized");
            this.nation = nation;
            console.log(nation.name);
            this.academyData = selectedNationData;
            this.nationYearData = await this.getNationYearData();
            this.createGraph();
            this.filteredAcademyData = this.academyData;
            this.totalAcademiesPages = Math.ceil(this.filteredAcademyData.length / 10);
            this.updateAcademies();

            // Mando i dati al genitore, per evitare nuovi fetch inutili
            this.$dispatch("useracademygraph-data", this.academyData);
        },
    };
};
