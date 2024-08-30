export const enablingresults = (eventid) => {
    return {
        submitEnablingResult: async function (resultid, result) {
            console.log("submitEnablingResult", resultid, result);

            const url = `/submit-enabling-result/${eventid}`;

            const fd = new FormData();
            fd.append("result_id", resultid);
            fd.append("result", result);

            const response = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: fd,
            });

            if (response.ok) {
                const data = await response.json();
                console.log("submitEnablingResult response", data);
                if (data.success) {
                    this.row.result = data.result.result;
                    this.row.stage = data.result.stage;
                }
            }

        },
        notesModal: {
            resultid: null,
            notes: null,
            showModal: false,
        },
        openNotesModal: function (resultid, notes) {
            this.notesModal.resultid = resultid;
            this.notesModal.notes = notes ? notes : "No notes";
            this.notesModal.showModal = true;
        },
        init: async function () {
        },
    };
};
