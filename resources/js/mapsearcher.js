import { all } from "axios";

export const mapsearcher = (academies) => {
    return {
        map: null,
        search: "",
        results: academies,
        allResults: academies,
        paginatedResults: [],
        currentPage: 1,
        totalPages: 0,
        nationFilter: "",
        markers: [],
        searchChanged: async function () {
            if (this.search.length < 3) return;

            const response = await fetch(
                `/academies-search?location=${this.search}`
            );

            this.results = await response.json();

            this.results.forEach((result) => {
                let latLng = JSON.parse(result.coordinates);

                let marker = new google.maps.Marker({
                    position: latLng,
                    map: this.map,
                });

                this.markers.push({
                    id: result.id,
                    marker: marker,
                });
            });
        },
        zoomToMarker: function (id) {
            let marker = this.markers.find((m) => m.id === id);

            if (marker) {
                this.map.panTo(marker.marker.getPosition());
                this.map.setZoom(15);
            }
        },
        paginateResults: function () {
            this.totalPages = Math.ceil(this.results.length / 5);
            this.paginatedResults = this.results.slice(
                (this.currentPage - 1) * 5,
                this.currentPage * 5
            );
        },
        nextPage: function () {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.paginateResults();
            }
        },
        prevPage: function () {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.paginateResults();
            }
        },
        fiterByNation: async function (nationId) {
            this.results = academies.filter((a) => a.nation_id == nationId);
            this.paginateResults();

            const nationCoordinates = this.results[0].coordinates;

            let marker = new google.maps.Marker({
                position: nationCoordinates,
                map: this.map,
            });

            this.map.panTo(marker.getPosition());
            this.map.setZoom(6);

            this.map.mapTypeId = "roadmap";
        },
        async init() {
            const { Map } = await google.maps.importLibrary("maps");

            this.map = new Map(document.getElementById("google-map"), {
                center: { lat: 45.46404, lng: 9.18938 },
                zoom: 2,
                mapTypeId: "satellite",
                mapTypeControlOptions: {
                    mapTypeIds: ["roadmap", "satellite", "hybrid", "terrain"],
                },
            });
            this.paginateResults();

            this.results.forEach((result) => {
                let marker = new google.maps.Marker({
                    position: result.coordinates,
                    map: this.map,
                });

                console.log(result.nation_id);

                this.markers.push({
                    id: result.id,
                    marker: marker,
                });
            });
        },
    };
};
