// Google Maps

export const googlemap = (location) => {
    //Idealmente è un array con latitudine e longitudine

    const fetchLocation = async (location) => {
        const response = await fetch(`/events/location?location=${location}`);
        const data = await response.json();
        return data;
    };

    const fetchCoordinates = async (address) => {
        const response = await fetch(`/events/coordinates?address=${address}`);
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
            console.log(`googlemap init ${location}`);

            fetchLocation(this.location).then(async (data) => {
                await new Promise((resolve) => {
                    const checkGoogle = setInterval(() => {
                        if (typeof google !== "undefined") {
                            clearInterval(checkGoogle);
                            resolve();
                        }
                    }, 100);
                });

                data.address_components.forEach((element) => {
                    if (element.types.includes("route")) {
                        this.address = element.long_name + ", " + this.address;
                    }

                    if (element.types.includes("street_number")) {
                        this.address += element.long_name;
                    }

                    if (this.address === "") {
                        if (element.types.includes("sublocality_level_2")) {
                            this.address = element.long_name;
                        }
                    }

                    if (element.types.includes("locality")) {
                        this.city = element.long_name;
                    }

                    if (this.city === "") {
                        if (element.types.includes("postal_town")) {
                            this.city = element.long_name;
                        }
                    }

                    if (this.city === "") {
                        if (
                            element.types.includes(
                                "administrative_area_level_2"
                            )
                        ) {
                            this.city = element.long_name;
                        }
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

                this.location = JSON.stringify(data);
            });
        },
    };
};
