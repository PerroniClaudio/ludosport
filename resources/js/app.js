import "./bootstrap";

import Alpine from "alpinejs";
import { chart } from "./chart.js";
import { editor } from "./editor.js";
import { googlemap } from "./googlemap.js";
import { calendar } from "./calendar.js";

String.prototype.deentitize = function () {
    var ret = this.replace(/&gt;/g, ">");
    ret = ret.replace(/&lt;/g, "<");
    ret = ret.replace(/&quot;/g, '"');
    ret = ret.replace(/&apos;/g, "'");
    ret = ret.replace(/&amp;/g, "&");
    return ret;
};

document.addEventListener("alpine:init", () => {
    Alpine.data("editor", editor);
    Alpine.data("googlemap", googlemap);
    Alpine.data("calendar", calendar);
    Alpine.data("chart", chart);
});

window.Alpine = Alpine;

Alpine.start();
