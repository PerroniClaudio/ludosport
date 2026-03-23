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
        
        if (!response.ok) {
            const error = new Error('Address not recognized');
            error.statusCode = response.status;
            throw error;
        }
        
        const data = await response.json();
        
        // Controlla se il server ha ritornato un errore di validazione
        if (data.error) {
            const error = new Error('Address not recognized');
            error.isValidationError = true;
            throw error;
        }
        
        return data;
    };


    const fetchGoogle = async () => new Promise((resolve) => {
        const checkGoogle = setInterval(() => {
            if (typeof google !== "undefined") {
                clearInterval(checkGoogle);
                resolve();
            }
        }, 100);
    });

    return {
        location: location || JSON.stringify({ lat: 0, lng: 0 }),
        city: "",
        address: "",
        postal_code: "",
        country: "",
        map: null,
        marker: null,
        searchStatus: null, // 'loading', 'success', 'error', 'warning'
        searchMessage: "",
        correctedData: null, // Salva i dati corretti per mostrare le differenze
        init() {
            console.log(`googlemap init ${location}`);
            
            // Carica i dati subito (indipendentemente da Google Maps)
            this.loadLocationData();
            
            // Se Google è già disponibile, inizializza la mappa
            if (typeof google !== "undefined" && google.maps) {
                this.initializeMap();
            } else {
                // Altrimenti, aspetta che Google Maps diventi disponibile
                this.waitForGoogleMaps();
            }
        },
        
        loadLocationData() {
            const location = this.location;
            fetchLocation(location).then(async (data) => {
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
                
                // Se la mappa è già inizializzata, aggiorna la visualizzazione
                if (this.map) {
                    this.map.setCenter(data.geometry.location);
                }
            });
        },
        
        waitForGoogleMaps() {
            const checkGoogle = setInterval(() => {
                if (typeof google !== "undefined" && google.maps) {
                    clearInterval(checkGoogle);
                    console.log('[googlemap] Google Maps available, initializing map');
                    this.initializeMap();
                }
            }, 100);
        },
        
        initializeMap() {
            const location = this.location;
            fetchLocation(location).then((data) => {
                if (!document.getElementById("eventGoogleMap")) {
                    console.warn('[googlemap] eventGoogleMap element not found');
                    return;
                }
                
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

            this.searchStatus = 'loading';
            this.searchMessage = '';
            this.correctedData = null;
            
            fetchCoordinates(newAddress).then((data) => {
                // Estrai i dati dai address_components
                let newCity = '';
                let newAddress = '';
                let newPostalCode = '';
                let newCountry = '';
                
                data.address_components.forEach((element) => {
                    if (element.types.includes("route")) {
                        newAddress = element.long_name + ", " + newAddress;
                    }
                    if (element.types.includes("street_number")) {
                        newAddress += element.long_name;
                    }
                    if (newAddress === "" && element.types.includes("sublocality_level_2")) {
                        newAddress = element.long_name;
                    }
                    if (element.types.includes("locality")) {
                        newCity = element.long_name;
                    }
                    if (newCity === "" && element.types.includes("postal_town")) {
                        newCity = element.long_name;
                    }
                    if (newCity === "" && element.types.includes("administrative_area_level_2")) {
                        newCity = element.long_name;
                    }
                    if (element.types.includes("country")) {
                        newCountry = element.long_name;
                    }
                    if (element.types.includes("postal_code")) {
                        newPostalCode = element.long_name;
                    }
                });
                
                // Aggiorna sempre le coordinate nell'input hidden
                this.location = JSON.stringify({ lat: data.lat, lng: data.lng });
                document.getElementById('eventLocationCoordinates').value = JSON.stringify({ lat: data.lat, lng: data.lng });
                
                // Controlla se ci sono differenze nei dati
                const hasDifferences = 
                    (newCity && newCity !== this.city) ||
                    (newAddress && newAddress !== this.address) ||
                    (newPostalCode && newPostalCode !== this.postal_code) ||
                    (newCountry && newCountry !== this.country);
                
                // Controlla le coordinate: se sono identiche o molto vicine, le differenze nei nomi sono probabilmente traslitterazioni
                let currentLocation = null;
                try {
                    const parsed = JSON.parse(document.getElementById('eventLocationCoordinates').value);
                    currentLocation = parsed;
                } catch (e) {
                    currentLocation = { lat: 0, lng: 0 };
                }
                
                // Calcola la distanza tra le vecchie e nuove coordinate (in km)
                const distance = this.calculateDistance(currentLocation.lat, currentLocation.lng, data.lat, data.lng);
                const hasMajorCoordinateChange = distance > 1; // Più di 1km di differenza
                
                // Filtra le differenze significative: esclude traslitterazioni di città se coordinate identiche
                let significantDifferences = [];
                
                if (newAddress && newAddress !== this.address) {
                    significantDifferences.push({
                        type: 'Address',
                        old: this.address,
                        new: newAddress,
                        always_show: true
                    });
                }
                
                if (newCity && newCity !== this.city) {
                    // Se coordinate identiche, potrebbe essere traslitterazione - mostra solo se cambia molto
                    const isCityTransliteration = !hasMajorCoordinateChange && 
                        newCity.toLowerCase().replace(/[àáâäã]/g, 'a').replace(/[èéêë]/g, 'e') ===
                        this.city.toLowerCase().replace(/[àáâäã]/g, 'a').replace(/[èéêë]/g, 'e');
                    
                    if (!isCityTransliteration) {
                        significantDifferences.push({
                            type: 'City',
                            old: this.city,
                            new: newCity,
                            always_show: true
                        });
                    }
                }
                
                if (newPostalCode && newPostalCode !== this.postal_code) {
                    significantDifferences.push({
                        type: 'ZIP',
                        old: this.postal_code,
                        new: newPostalCode,
                        always_show: true
                    });
                }
                
                if (newCountry && newCountry !== this.country) {
                    significantDifferences.push({
                        type: 'Country',
                        old: this.country,
                        new: newCountry,
                        always_show: true
                    });
                }
                
                if (significantDifferences.length > 0) {
                    // Aggiorna subito i campi nel form con i dati corretti
                    if (newAddress && newAddress !== this.address) {
                        this.address = newAddress;
                    }
                    if (newCity && newCity !== this.city) {
                        this.city = newCity;
                    }
                    if (newPostalCode && newPostalCode !== this.postal_code) {
                        this.postal_code = newPostalCode;
                    }
                    if (newCountry && newCountry !== this.country) {
                        this.country = newCountry;
                    }
                    
                    // Mostra warning solo se ci sono differenze significative
                    const differences = significantDifferences.map(d => 
                        `${d.type}: "${d.old}" → "${d.new}"`
                    );
                    
                    this.searchStatus = 'warning';
                    this.correctedData = {
                        city: newCity,
                        address: newAddress,
                        postal_code: newPostalCode,
                        country: newCountry
                    };
                    this.searchMessage = 'Address corrected: ' + differences.join('; ') + '. Remember to save.';
                    
                    console.log('[googlemap] Fields updated with corrected data:', significantDifferences);
                } else {
                    // Feedback di successo
                    this.searchStatus = 'success';
                    this.searchMessage = 'Address correctly identified. Remember to save.';
                }
                
                // Se la mappa esiste e Google APIs è accettato, aggiorna la visualizzazione
                if (this.map) {
                    this.map.setCenter({ lat: data.lat, lng: data.lng });
                    this.map.setZoom(15);

                    // Rimuovi il marker precedente
                    if (this.marker !== null) {
                        this.marker.setMap(null);
                    }

                    // Aggiungi il nuovo marker
                    this.marker = new google.maps.Marker({
                        position: { lat: data.lat, lng: data.lng },
                        map: this.map,
                    });
                    
                    console.log('[googlemap] Map updated with new coordinates:', data);
                } else {
                    console.log('[googlemap] Map not initialized (Google APIs not accepted), but coordinates updated:', data);
                }
                
                // Resetta lo stato dopo 3 secondi per warning, 2 per success
                const timeout = this.searchStatus === 'warning' ? 3000 : 2000;
                setTimeout(() => {
                    this.searchStatus = null;
                    this.searchMessage = '';
                }, timeout);
            }).catch((error) => {
                // Determina il tipo di errore e mostra il messaggio appropriato
                let errorMessage = 'Address not recognized. Try with a different address.';
                
                if (error.statusCode === 500 || error.message === 'Failed to fetch') {
                    errorMessage = 'Server error. Try again later or with a different address.';
                }
                
                this.searchStatus = 'error';
                this.searchMessage = errorMessage;
                console.error('[googlemap] Error fetching coordinates:', error);
                
                // Resetta lo stato dopo 3 secondi
                setTimeout(() => {
                    this.searchStatus = null;
                    this.searchMessage = '';
                }, 3000);
            });
        },
        
        calculateDistance(lat1, lon1, lat2, lon2) {
            // Formula di Haversine per calcolare la distanza tra due coordinate (in km)
            const R = 6371; // Raggio della Terra in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        },
    };
};
