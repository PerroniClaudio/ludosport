export const mapsearcher = (academies) => {
    return {
        createSchoolMarker: function(result) {
            this.infoWindowPinned = false;
            let latLng = typeof result.coordinates === "string"
                ? JSON.parse(result.coordinates)
                : result.coordinates;
            let marker = new google.maps.Marker({
                position: latLng,
                map: this.map,
            });
            // Crea una InfoWindow con il nome della scuola
            let address = result.address || "";
            let city = result.city || "";
            let state = result.state || "";
            let zip = result.zip || "";
            let country = result.country || "";
            let lat = (latLng && latLng.lat) || "";
            let lng = (latLng && latLng.lng) || "";
            let mapsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(`${lat},${lng}`)}`;
            let infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style='font-size:16px;font-weight:bold;'>
                        ${result.name || ""}
                    </div>
                    <div style='font-size:14px;'>
                        ${address}<br>
                        ${city}${city && state ? ', ' : ''}${state} ${zip}<br>
                        ${country}
                    </div>
                    <div class="text-primary-500 font-semibold border-none focus:outline-none" style='margin-top:8px;'>
                        <a href="${mapsUrl}" target="_blank" rel="noopener">Get directions on Google Maps</a>
                    </div>
                `
            });
            marker.addListener("click", () => {
                this.zoomToMarker(result.id);
                infoWindow.open(this.map, marker);
                this.infoWindowPinned = true;
            });
            infoWindow.addListener("closeclick", () => {
                this.infoWindowPinned = false;
            });
            marker.addListener("mouseover", () => {
                infoWindow.open(this.map, marker);
            });
            marker.addListener("mouseout", () => {
                if (!this.infoWindowPinned) {
                    infoWindow.close();
                }
            });
            this.map.addListener("click", () => {
                infoWindow.close();
                this.infoWindowPinned = false;
            });
            this.markers.push({
                id: result.id,
                marker: marker,
                infoWindow: infoWindow,
            });
        },
        map: null,
        search: "",
        results: academies,
        allResults: academies,
        paginatedResults: [],
        currentPage: 1,
        totalPages: 0,
        nationFilter: "",
        markers: [],
        infoWindowPinned: false,

        searchChanged: async function () {
            if (this.search.length === 0) {
                // Rimuovi tutti i marker dalla mappa
                this.markers.forEach((m) => m.marker.setMap(null));
                this.markers = [];
                // Ripristina risultati e paginazione
                this.results = this.allResults;
                this.currentPage = 1;
                this.paginateResults();
                return;
            }

            if (this.search.length < 3) return;

            const response = await fetch(
                `/schools-search?location=${this.search}`
            );

            this.results = await response.json();
            this.paginateResults();

            // Rimuovi i marker precedenti
            this.markers.forEach((m) => m.marker.setMap(null));
            this.markers = [];

            this.results.forEach((result) => {
                this.createSchoolMarker(result);
            });

            if (this.results[0] && this.results[0].id) {
                this.zoomToMarker(this.results[0].id);
            }
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
                this.createSchoolMarker(result);
            });
        },
    };
};
