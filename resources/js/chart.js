// Chart component

export const chart = (data) => {
    return {
        data: data,
        showingDataForDate: new Date(),
        previousMonth: function () {
            let newDate = new Date(this.showingDataForDate);
            newDate.setMonth(newDate.getMonth() - 1);

            let month = newDate.getMonth() + 1;

            if (month < 10) {
                month = "0" + month;
            }

            const url = `/rankings/paginate?date=${newDate.getFullYear()}-${month}`;

            fetch(url)
                .then((data) => data.json())
                .then((res) => {
                    this.data = res;
                    this.showingDataForDate = newDate;
                    this.init();
                })
                .catch((e) => console.log(e));
        },
        nextMonth: function () {
            let newDate = new Date(this.showingDataForDate);
            newDate.setMonth(newDate.getMonth() + 1);

            let month = newDate.getMonth() + 1;

            if (month < 10) {
                month = "0" + month;
            }

            const url = `/rankings/paginate?date=${newDate.getFullYear()}-${month}`;

            fetch(url)
                .then((data) => data.json())
                .then((res) => {
                    this.data = res;
                    this.showingDataForDate = newDate;
                    this.init();
                })
                .catch((e) => console.log(e));
        },
        currentPage: 1,
        pageSize: 10,
        totalItems: 0,
        totalPages: 0,
        paginatedData: [],
        paginateData() {
            console.log(this.data);

            const startIndex = (this.currentPage - 1) * this.pageSize;
            const endIndex = startIndex + this.pageSize;
            this.paginatedData = this.data.slice(startIndex, endIndex);
            this.totalItems = this.data.length;
            this.totalPages = Math.ceil(this.totalItems / this.pageSize);
        },
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.paginateData();
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.paginateData();
            }
        },
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.paginateData();
            }
        },
        init() {
            this.paginateData();
        },
    };
};
