import "./bootstrap";

import Alpine from "alpinejs";
import AsyncAlpine from "async-alpine";

Alpine.plugin(AsyncAlpine);

document.addEventListener("alpine:init", () => {
    Alpine.asyncData("editor", () => import("./editor.js"));
    Alpine.asyncData("googlemap", () => import("./googlemap.js"));
    Alpine.asyncData("calendar", () => import("./calendar.js"));
    Alpine.asyncData("participants", () => import("./participants.js"));
    Alpine.asyncData("eventpersonnel", () => import("./eventpersonnel.js"));
    Alpine.asyncData("mapsearcher", () => import("./mapsearcher.js"));
    Alpine.asyncData("usernationgraphadmin", () =>
        import("./usernationgraphadmin.js")
    );
    Alpine.asyncData("useracademygraphadmin", () =>
        import("./useracademygraphadmin.js")
    );
    Alpine.asyncData("userschoolgraphadmin", () =>
        import("./userschoolgraphadmin.js")
    );
    Alpine.asyncData("usercoursegraphadmin", () =>
        import("./usercoursegraphadmin.js")
    );
    Alpine.asyncData("eventsparticipantsgraph", () =>
        import("./eventsparticipantsgraph.js")
    );
    Alpine.asyncData("userschoolgraph", () => import("./userschoolgraph.js"));
    Alpine.asyncData("usersclangraph", () => import("./usersclangraph.js"));
    Alpine.asyncData("rankingschart", () => import("./rankingschart.js"));
    Alpine.asyncData("enablingresults", () => import("./enablingresults.js"));
});

window.Alpine = Alpine;

document.addEventListener("click", function (event) {
    let expandable = document.querySelector("#bigone");

    if (expandable) {
        if (event.target.tagName === "A" && event.target.hasAttribute("href")) {
            
            if (event.target.classList.contains("external")){
                return;
            }

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
