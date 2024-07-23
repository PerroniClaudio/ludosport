export const participants = (eventid, role) => {
    return {
        eventid,
        participants: [],
        availableUsers: [],
        paginatedUsers: [],
        currentPage: 1,
        pageSize: 10,
        totalPages: 0,
        getAvailableUsers: async function () {
            console.log("getAvailableUsers");

            const url = `${ role == 'admin' ? '' : '/' + role }/events/${this.eventid}/available-users`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                if (this.participants.length > 0) {
                    this.availableUsers = data.filter((user) =>
                            !this.participants.some((participant) => participant.id === user.id)
                    );
                } else {
                    this.availableUsers = data;
                }
            }
        },
        getParticipants: async function () {
            console.log("getParticipants");

            const url = `${ role == 'admin' ? '' : '/' + role }/events/${this.eventid}/participants`;
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                this.participants = data;
                if (this.availableUsers.length > 0) {
                    this.availableUsers = this.availableUsers.filter((user) =>
                            !this.participants.some((participant) => participant.id === user.id)
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
        searchParticipants: function (search) {
            console.log("searchParticipants");

            return this.participants.filter((user) => {
                return (
                    user.name.toLowerCase().includes(search.toLowerCase()) ||
                    user.surname.toLowerCase().includes(search.toLowerCase())
                );
            });
        },
        addParticipant: async function (userid) {
            console.log("addParticipant");
            const user = this.availableUsers.find((user) => user.id === userid);
            if (user) {
                this.participants.push(user);
            }

            let participants_id = JSON.stringify(
                this.participants.map((user) => user.id)
            );

            const url = `${ role == 'admin' ? '' : '/' + role }/add-participants`;

            const fd = new FormData();
            fd.append("event_id", this.eventid);
            fd.append("participants", participants_id);

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
        removeParticipant: async function (userid) {
            console.log("removeParticipant");
            const user = this.participants.find((user) => user.id === userid);
            if (user) {
                this.participants = this.participants.filter(part => part.id !== userid);
            }

            let participants_id = JSON.stringify(
                this.participants.map((user) => user.id)
            );

            const url = `${ role == 'admin' ? '' : '/' + role }/add-participants`;

            const fd = new FormData();
            fd.append("event_id", this.eventid);
            fd.append("participants", participants_id);

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
        saveParticipants: async function () {
            console.log("saveParticipants");
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
            await this.getParticipants();
            this.paginateAvailableUsers();
        },
    };
};
