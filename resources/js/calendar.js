// FullCalendar

import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";

// FullCalendar

export const calendar = (eventSource) => {
    return {
        calendar: null,
        approved_events: [],
        pending_events: [],
        init() {
            this.calendar = new Calendar(this.$refs.calendar, {
                plugins: [dayGridPlugin],
                initialView: "dayGridMonth",
                events: eventSource,
                height: "auto",
                eventClick: function (info) {
                    window.location.href = info.event.url;
                },
                eventSourceSuccess: (content, response) => {
                    this.approved_events = [];
                    this.pending_events = [];

                    content.map((event) => {
                        if (event.is_approved) {
                            this.approved_events.push(event);
                        } else {
                            this.pending_events.push(event);
                        }
                    });
                    return content.eventArray;
                },
            });

            this.calendar.render();
        },
    };
};
