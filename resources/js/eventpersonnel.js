export const eventpersonnel = (eventid, role) => {
    return {
        eventid,
        eventPersonnel: [],
        availableUsers: [],
        paginatedUsers: [],
        currentPage: 1,
        pageSize: 10,
        totalPages: 0,
        getAvailableUsers: async function () {
            console.log("getAvailableUsers");

            const url = `${ role == 'admin' ? '' : '/' + role }/events/${this.eventid}/available-personnel`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                if (this.eventPersonnel.length > 0) {
                    this.availableUsers = data.filter((user) =>
                            !this.eventPersonnel.some((person) => person.id === user.id)
                    );
                } else {
                    this.availableUsers = data;
                }
            }
        },
        getPersonnel: async function () {
            console.log("getPersonnel");

            const url = `${ role == 'admin' ? '' : '/' + role }/events/${this.eventid}/personnel`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();

                this.eventPersonnel = data;
                if (this.availableUsers.length > 0) {
                    this.availableUsers = this.availableUsers.filter((user) =>
                            !this.eventPersonnel.some((person) => person.id === user.id)
                    );
                }
            }
        },
        searchAvailableUsers: function (search) {
            let searchvalue = search.target.value;

            if (searchvalue.length === 0) {
                this.paginateAvailableUsers();
                return;
            }

            this.paginatedUsers = this.availableUsers.filter((user) => {
                return (
                    user.name
                        .toLowerCase()
                        .includes(searchvalue.toLowerCase()) ||
                    user.surname
                        .toLowerCase()
                        .includes(searchvalue.toLowerCase())
                );
            });
        },
        searchPersonnel: function (search) {
            console.log("searchPersonnel");

            return this.eventPersonnel.filter((user) => {
                return (
                    user.name.toLowerCase().includes(search.toLowerCase()) ||
                    user.surname.toLowerCase().includes(search.toLowerCase())
                );
            });
        },
        addPersonnel: async function (userid) {
            console.log("addPersonnel");
            const user = this.availableUsers.find((user) => user.id === userid);
            if (user) {
                this.eventPersonnel.push(user);
            }

            let personnel_id = JSON.stringify(
                this.eventPersonnel.map((user) => user.id)
            );

            const url = `${ role == 'admin' ? '' : '/' + role }/events/${this.eventid}/add-personnel`;

            const fd = new FormData();
            fd.append("event_id", this.eventid);
            fd.append("personnel", personnel_id);

            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: fd,
            });

            this.availableUsers = this.availableUsers.filter(
                (user) => user.id !== userid
            );
            this.paginateAvailableUsers();
        },
        removePersonnel: async function (userid) {
            console.log("removePersonnel");
            console.log(this.eventPersonnel)
            const user = this.eventPersonnel.find((user) => user.id === userid);
            if (user) {
                this.eventPersonnel = this.eventPersonnel.filter(part => part.id !== userid);
            }

            let personnel_id = JSON.stringify(
                this.eventPersonnel.map((user) => user.id)
            );

            const url = `${ role == 'admin' ? '' : '/' + role }/events/${this.eventid}/add-personnel`;

            const fd = new FormData();
            fd.append("event_id", this.eventid);
            fd.append("personnel", personnel_id);

            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: fd,
            });

            this.availableUsers.push(user);
            this.paginateAvailableUsers();
        },
        savePersonnel: async function () {
            console.log("savePersonnel");
        },

        paginateAvailableUsers: function () {
            const startIndex = (this.currentPage - 1) * this.pageSize;
            const endIndex = startIndex + this.pageSize;
            this.paginatedUsers = this.availableUsers.slice(
                startIndex,
                endIndex
            );
            this.totalPages = Math.ceil(
                this.availableUsers.length / this.pageSize
            );
        },
        goToPage: function (page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.paginateAvailableUsers();
            }
        },
        init: async function () {
            await this.getAvailableUsers();
            await this.getPersonnel();
            this.paginateAvailableUsers();
        },
    };
};
