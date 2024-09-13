import Chart from "chart.js/auto";

export const usercoursegraphadmin = (role, school, selectedSchoolData) => {
    return {
        school: null,
        courseData: [],
        filteredCourseData: [],
        schoolYearData: [],
        currentCoursesPage: 1,
        totalCoursesPages: 1,
        paginatedCourses: [],
        colors: [
            "rgb(237,116,0)",
            "rgb(212, 145, 255)",
            "rgb(179,4,16)",
            "rgb(0,94,152)",
            "rgb(0,129,57)",
        ],
        async getSchoolYearData() {
            const response = await fetch(
                `${role == 'admin' ? '' : '/' + role}/schools/${school.id}/athletes-year-data`
            );
            const data = await response.json();

            return data;
        },
        createGraph() {
            const ctx = document
                .getElementById("usercoursegraph")
                .getContext("2d");

            const labels = this.courseData.map((course) => course.name);
            const dataCount = this.courseData.map((course) => course.athletes);

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

        searchCourseByValue(e) {
            const searchValue = e.target.value.toLowerCase();
            if (searchValue != '') {
                this.filteredCourseData = this.courseData.filter(row => {
                    return row.name.toLowerCase().includes(searchValue);
                });
                this.updateCourses();
            } else {
                this.filteredCourseData = this.courseData;
                this.updateCourses();
            }
        },
        
        
        nextPage() {
            if (this.currentCoursesPage < this.totalCoursesPages) {
                this.currentCoursesPage++;
                this.updateCourses();
            }
        },
        previousPage() {
            if (this.currentCoursesPage > 1) {
                this.currentCoursesPage--;
                this.updateCourses();
            }
        },
        updateCourses() {
            const offset = (this.currentCoursesPage - 1) * 10; // Assuming 10 items per page
            // this.paginatedCourses = this.courseData.slice(offset, offset + 10);

            this.totalCoursesPages = Math.ceil(this.filteredCourseData.length / 10);
            this.paginatedCourses = this.filteredCourseData.slice(offset, offset + 10);
        },
        
        async init() {
            console.log("usercoursegraph initialized");
            this.school = school;
            console.log(school.name);
            this.courseData = selectedSchoolData;
            this.schoolYearData = await this.getSchoolYearData();
            this.createGraph();
            this.filteredCourseData = this.courseData;
            this.totalCoursesPages = Math.ceil(this.filteredCourseData.length / 10);
            this.updateCourses();

            // Mando i dati al genitore, per evitare nuovi fetch inutili
            this.$dispatch("usercoursegraph-data", this.courseData);
        },
    };
};
