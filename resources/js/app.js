document.addEventListener("alpine:init", async () => {
    const { usercoursegraphadmin } = await import("./usercoursegraphadmin.js");
    const { eventsparticipantsgraph } = await import(
        "./eventsparticipantsgraph.js"
    );
    const { userschoolgraph } = await import("./userschoolgraph.js");
    const { usersclangraph } = await import("./usersclangraph.js");
    const { rankingschart } = await import("./rankingschart.js");
    const { eventpersonnel } = await import("./eventpersonnel.js");
    const { enablingresults } = await import("./enablingresults.js");

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
