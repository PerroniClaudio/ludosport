export const mapsearcher = () => {
    return {
        map: null,
        search: "",
        results: [],
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
        async init() {
            const { Map } = await google.maps.importLibrary("maps");

            this.map = new Map(document.getElementById("google-map"), {
                center: { lat: 45.46404, lng: 9.18938 },
                zoom: 10,
            });
        },
    };
};
