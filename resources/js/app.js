import "./bootstrap";

import Alpine from "alpinejs";
import { chart } from "./chart.js";
import { editor } from "./editor.js";
import { googlemap } from "./googlemap.js";
import { calendar } from "./calendar.js";
import { participants } from "./participants.js";
import { mapsearcher } from "./mapsearcher.js";
import { usernationgraphadmin } from "./usernationgraphadmin.js";
import { useracademygraphadmin } from "./useracademygraphadmin.js";
import { userschoolgraphadmin } from "./userschoolgraphadmin.js";
import { usercoursegraphadmin } from "./usercoursegraphadmin.js";
import { eventsparticipantsgraph } from "./eventsparticipantsgraph.js";
import { userschoolgraph } from "./userschoolgraph.js";
import { usersclangraph } from "./usersclangraph.js";
import { rankingschart } from "./rankingschart.js";
import { eventpersonnel } from "./eventpersonnel.js";
import { enablingresults } from "./enablingresults.js";

document.addEventListener("alpine:init", () => {
    Alpine.data("editor", editor);
    Alpine.data("googlemap", googlemap);
    Alpine.data("calendar", calendar);
    Alpine.data("chart", chart);
    Alpine.data("participants", participants);
    Alpine.data("eventpersonnel", eventpersonnel);
    Alpine.data("mapsearcher", mapsearcher);
    Alpine.data("usernationgraphadmin", usernationgraphadmin);
    Alpine.data("useracademygraphadmin", useracademygraphadmin);
    Alpine.data("userschoolgraphadmin", userschoolgraphadmin);
    Alpine.data("usercoursegraphadmin", usercoursegraphadmin);
    Alpine.data("eventsparticipantsgraph", eventsparticipantsgraph);
    Alpine.data("userschoolgraph", userschoolgraph);
    Alpine.data("usersclangraph", usersclangraph);
    Alpine.data("rankingschart", rankingschart);
    Alpine.data("enablingresults", enablingresults);
});

window.Alpine = Alpine;

document.addEventListener("click", function (event) {
    let expandable = document.querySelector("#bigone");

    if (expandable) {
        if (event.target.tagName === "A" && event.target.hasAttribute("href")) {
            event.preventDefault();

            let reduceble = document.querySelector("#smallone");

            expandable.classList.add("expand");
            reduceble.classList.add("reduce");

            setTimeout(() => {
                window.location.href = event.target.getAttribute("href");
            }, 1000);
        }
    }
});

Alpine.start();
