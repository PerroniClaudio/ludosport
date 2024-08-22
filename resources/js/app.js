import "./bootstrap";

import Alpine from "alpinejs";
import { chart } from "./chart.js";
import { editor } from "./editor.js";
import { googlemap } from "./googlemap.js";
import { calendar } from "./calendar.js";
import { participants } from "./participants.js";
import { mapsearcher } from "./mapsearcher.js";
import { userschoolgraph } from "./userschoolgraph.js";
import { usersclangraph } from "./usersclangraph.js";
import { rankingschart } from "./rankingschart.js";
import { eventpersonnel } from "./eventpersonnel.js";

document.addEventListener("alpine:init", () => {
    Alpine.data("editor", editor);
    Alpine.data("googlemap", googlemap);
    Alpine.data("calendar", calendar);
    Alpine.data("chart", chart);
    Alpine.data("participants", participants);
    Alpine.data("eventpersonnel", eventpersonnel);
    Alpine.data("mapsearcher", mapsearcher);
    Alpine.data("userschoolgraph", userschoolgraph);
    Alpine.data("usersclangraph", usersclangraph);
    Alpine.data("rankingschart", rankingschart);
});

window.Alpine = Alpine;

Alpine.start();
