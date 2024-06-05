import "./bootstrap";

import Alpine from "alpinejs";

// Tiptap Editor

import { Editor } from "@tiptap/core";
import StarterKit from "@tiptap/starter-kit";
import Link from "@tiptap/extension-link";

// FullCalendar

import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";

String.prototype.deentitize = function () {
    var ret = this.replace(/&gt;/g, ">");
    ret = ret.replace(/&lt;/g, "<");
    ret = ret.replace(/&quot;/g, '"');
    ret = ret.replace(/&apos;/g, "'");
    ret = ret.replace(/&amp;/g, "&");
    return ret;
};

document.addEventListener("alpine:init", () => {
    // TipTap Editor

    Alpine.data("editor", (content) => {
        let editor; // Alpine's reactive engine automatically wraps component properties in proxy objects. Attempting to use a proxied editor instance to apply a transaction will cause a "Range Error: Applying a mismatched transaction", so be sure to unwrap it using Alpine.raw(), or simply avoid storing your editor as a component property, as shown in this example.

        return {
            updatedAt: Date.now(), // force Alpine to rerender on selection change
            init() {
                const _this = this;

                editor = new Editor({
                    element: this.$refs.element,
                    extensions: [
                        StarterKit.configure({
                            heading: {
                                HTMLAttributes: {
                                    class: "text-2xl font-bold",
                                },
                            },
                        }),
                        Link,
                    ],
                    content: content.deentitize(),
                    editable: true,
                    autofocus: true,
                    editorProps: {
                        attributes: {
                            class: "w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:outline-none focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm",
                        },
                    },
                    onCreate({ editor }) {
                        _this.updatedAt = Date.now();
                    },
                    onUpdate({ editor }) {
                        _this.updatedAt = Date.now();
                        const html = editor.getHTML();
                        const element =
                            document.querySelector("#editor-content");
                        element.value = html;
                    },
                    onSelectionUpdate({ editor }) {
                        _this.updatedAt = Date.now();
                    },
                });
            },
            isLoaded() {
                return editor;
            },
            isActive(type, opts = {}) {
                return editor.isActive(type, opts);
            },
            toggleHeading(opts) {
                editor.chain().toggleHeading(opts).focus().run();
            },
            toggleBold() {
                editor.chain().focus().toggleBold().run();
            },
            toggleItalic() {
                editor.chain().focus().toggleItalic().run();
            },
            toggleStrike() {
                editor.chain().focus().toggleStrike().run();
            },
            toggleParagraph() {
                editor.chain().focus().toggleParagraph().run();
            },
            toggleBulletList() {
                editor.chain().focus().toggleBulletList().run();
            },
            toggleOrderedList() {
                editor.chain().focus().toggleOrderedList().run();
            },
            toggleBlockquote() {
                editor.chain().focus().toggleBlockquote().run();
            },
            toggleHorizontalRule() {
                editor.chain().focus().setHorizontalRule().run();
            },
            toggleLink() {
                editor
                    .chain()
                    .focus()
                    .toggleLink({ href: "https://example.com" })
                    .run();
            },
            undo() {
                editor.chain().focus().undo().run();
            },
            redo() {
                editor.chain().focus().redo().run();
            },
            getActiveHeadingLevel(updatedAt) {
                if (this.updatedAt !== updatedAt) {
                    return;
                }

                return editor.isActive("heading", { level: 1 })
                    ? 1
                    : editor.isActive("heading", { level: 2 })
                    ? 2
                    : editor.isActive("heading", { level: 3 })
                    ? 3
                    : editor.isActive("heading", { level: 4 })
                    ? 4
                    : editor.isActive("heading", { level: 5 })
                    ? 5
                    : editor.isActive("heading", { level: 6 })
                    ? 6
                    : 0;
            },
        };
    });

    // Google Maps

    Alpine.data("googlemap", (location) => {
        //Idealmente è un array con latitudine e longitudine

        const fetchLocation = async (location) => {
            const response = await fetch(
                `/events/location?location=${location}`
            );
            const data = await response.json();
            return data;
        };

        const fetchCoordinates = async (address) => {
            const response = await fetch(
                `/events/coordinates?address=${address}`
            );
            const data = await response.json();
            return data;
        };

        return {
            location: location || JSON.stringify({ lat: 0, lng: 0 }),
            city: "",
            address: "",
            postal_code: "",
            country: "",
            map: null,
            marker: null,
            init() {
                fetchLocation(this.location).then((data) => {
                    data.address_components.forEach((element) => {
                        if (element.types.includes("route")) {
                            this.address =
                                element.long_name + ", " + this.address;
                        }

                        if (element.types.includes("street_number")) {
                            this.address += element.long_name;
                        }

                        if (element.types.includes("locality")) {
                            this.city = element.long_name;
                        }

                        if (element.types.includes("country")) {
                            this.country = element.long_name;
                        }

                        if (element.types.includes("postal_code")) {
                            this.postal_code = element.long_name;
                        }
                    });

                    this.map = new google.maps.Map(
                        document.getElementById("eventGoogleMap"),
                        {
                            center: data.geometry.location,
                            zoom: 15,
                            height: "400px",
                        }
                    );

                    this.marker = new google.maps.Marker({
                        position: data.geometry.location,
                        map: this.map,
                    });
                });
            },
            updateMap: function () {
                let newAddress =
                    this.address +
                    ", " +
                    this.city +
                    ", " +
                    this.postal_code +
                    ", " +
                    this.country;

                fetchCoordinates(newAddress).then((data) => {
                    this.map.setCenter(data);
                    this.map.setZoom(15);

                    // Rimuovi tutti i marker

                    if (this.marker !== null) {
                        this.marker.setMap(null);
                    }

                    // Aggiungi un marker

                    this.marker = new google.maps.Marker({
                        position: data,
                        map: this.map,
                    });

                    this.location = JSON.stringify({
                        lat: data.lat,
                        lng: data.lng,
                    });
                });
            },
        };
    });

    // FullCalendar

    Alpine.data("calendar", (eventSource) => {
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
    });

    // Chart component

    Alpine.data("chart", (data) => {
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
    });
});

window.Alpine = Alpine;

Alpine.start();
