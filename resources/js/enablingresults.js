export const enablingresults = (eventid) => {
    return {
        submitEnablingResult: async function (resultid, result, retake = null) {

            const url = `/submit-enabling-result/${eventid}`;

            const fd = new FormData();
            fd.append("result_id", resultid);
            fd.append("result", result);
            fd.append("retake", retake);

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
                if (data.success) {
                    this.row.result = data.result.result;
                    this.row.stage = data.result.stage;
                    this.row.retake = data.result.retake;
                }
            }

        },
        notesModal: {
            resultid: null,
            notes: null,
            internshipDuration: null,
            internshipNotes: null,
            retake: null,
        },
        openNotesModal: function (resultid, notes = null, internshipDuration = null, internshipNotes = null, retake = null) {
            this.notesModal.resultid = resultid;
            this.notesModal.notes = notes;
            this.notesModal.internshipDuration = internshipDuration;
            this.notesModal.internshipNotes = internshipNotes;
            this.notesModal.retake = retake;
        },
        init: async function () {
        },
    };
};
