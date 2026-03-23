<x-website-layout>

    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12" x-data="{
            googleMapsReady: false,
            googleMapsAccepted: false,
            googleMapsLoading: false,
            
            init() {
                this.checkAndLoadGoogleMaps();
                
                // Polling per controllare se Google Maps è caricato
                const interval = setInterval(() => {
                    if (window.google && window.google.maps) {
                        console.log('[schools-map] Google Maps is ready!');
                        this.googleMapsReady = true;
                        clearInterval(interval);
                    }
                }, 100);
                
                // Ascolta i cambiamenti di localStorage
                window.addEventListener('storage', (e) => {
                    if (e.key === 'policyChoices') {
                        console.log('[schools-map] Preferences updated from another tab/window');
                        this.checkAndLoadGoogleMaps();
                    }
                });
                
                // Ascolta l'evento custom dal banner
                window.addEventListener('policyChoicesUpdated', (e) => {
                    console.log('[schools-map] Preferences updated from banner:', e.detail.choices);
                    this.checkAndLoadGoogleMaps();
                });
            },
            
            checkAndLoadGoogleMaps() {
                const policyChoices = JSON.parse(localStorage.getItem('policyChoices') || '{}');
                const isAccepted = policyChoices.cookie_policy?.categories?.google_api === true;
                this.googleMapsAccepted = isAccepted;
                
                console.log('[schools-map] Google APIs accepted:', isAccepted);
                
                if (isAccepted && !window.google && !this.googleMapsLoading) {
                    this.loadGoogleMaps();
                } else if (isAccepted && window.google && window.google.maps) {
                    this.googleMapsReady = true;
                } else {
                    // API non accettate: resetta lo stato della mappa
                    this.googleMapsReady = false;
                    console.log('[schools-map] Google Maps disabled');
                }
            },
            
            loadGoogleMaps() {
                if (this.googleMapsLoading) {
                    console.log('[schools-map] Google Maps already loading...');
                    return;
                }
                
                // Controlla se Google Maps è già disponibile
                if (window.google && window.google.maps) {
                    console.log('[schools-map] Google Maps already available');
                    this.googleMapsReady = true;
                    return;
                }
                
                // Controlla se lo script Google Maps è già stato aggiunto al DOM
                if (document.getElementById('google-maps-script')) {
                    console.log('[schools-map] Google Maps script already in DOM');
                    return;
                }
                
                // Controlla se c'è già uno script con lo stesso src
                const scripts = document.getElementsByTagName('script');
                for (let script of scripts) {
                    if (script.src && script.src.includes('maps.googleapis.com/maps/api/js')) {
                        console.log('[schools-map] Google Maps script already present in page');
                        return;
                    }
                }
                
                this.googleMapsLoading = true;
                console.log('[schools-map] Loading Google Maps...');
                const script = document.createElement('script');
                script.id = 'google-maps-script';
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('app.google.maps_key') }}&v=weekly';
                script.async = true;
                script.defer = false;
                
                script.onload = () => {
                    console.log('[schools-map] Google Maps script loaded');
                    setTimeout(() => {
                        this.googleMapsReady = true;
                    }, 100);
                };
                
                script.onerror = () => {
                    console.error('[schools-map] Failed to load Google Maps');
                    this.googleMapsLoading = false;
                };
                
                document.head.appendChild(script);
            }
        }" x-init="init()">>
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.schools_map') }}
            </h1>

            <p class="text-background-800 dark:text-background-200 text-justify">{{ __('website.schools_map_text') }}
            </p>

            <!-- Mappa Container: solo visibile se Google APIs accettate -->
            <template x-if="googleMapsReady">
                <div class="h-[600px] w-full mt-8 rounded overflow-hidden" x-load 
                    x-data="mapsearcher({{ $schools_json }})"
                    x-init="setTimeout(() => init(), 100); $watch('nationFilter', (value) => fiterByNation(value))">
                    <div id="google-map" class="h-full w-full"></div>
                </div>
            </template>

            <!-- Messaggio giallo se Google APIs non accettate -->
            <template x-if="!googleMapsAccepted">
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/50 p-6 rounded-lg flex flex-col gap-4 items-start h-fit mt-8">
                    <div>
                        <h3 class="text-base font-semibold text-yellow-800 dark:text-yellow-100 mb-2">
                            {{ __('website.cookies_google_apis_required') ?? 'Google APIs Required' }}
                        </h3>
                        <p class="text-yellow-700 dark:text-yellow-200 text-sm">
                            {{ __('website.cookies_google_apis_message') ?? 'To view the schools, you need to enable Google APIs in your cookie preferences.' }}
                        </p>
                    </div>
                    <button @click="typeof window.openCookiePreferences === 'function' && window.openCookiePreferences()"
                        class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded hover:bg-primary-700 transition">
                        {{ __('website.cookies_manage_preferences') ?? 'Manage Preferences' }}
                    </button>
                </div>
            </template>

            <!-- Lista delle scuole (sempre visibile) -->
            <div class="flex flex-col gap-4 rounded mt-8" x-load x-data="mapsearcher({{ $schools_json }})"
                x-init="paginateResults(); $watch('nationFilter', (value) => fiterByNation(value))">
                
                <div class="flex flex-col gap-2">
                    <div class="w-full p-2">
                        <x-form.select name="country" label="{{ __('website.schools_map_nations') }}"
                            x-model="nationFilter" shouldHaveEmptyOption="true" :options="$nations" />
                    </div>

                    <div class="flex items-center gap-2 p-2">
                        <input type="text" placeholder="City/Zip Code" id="search"
                            class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
                            x-model="search" @input="searchChanged">
                        <button class="bg-primary-500 text-white rounded p-2" @click="searchChanged">
                            <x-lucide-search class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="flex flex-col gap-2 p-2">
                        <template x-for="school in paginatedResults" :key="school.id">
                            <a x-bind:href="'{{ env('APP_URL') }}/school-profile/' + school.slug">
                                <div
                                    class="rounded bg-white dark:bg-background-800 dark:text-background-300 p-4 flex flex-row justify-between gap-2">
                                    <div class="flex flex-col gap-1">
                                        <h1 class="font-bold dark:text-background-100" x-text="school.name"></h1>
                                        <p x-text="school.address"></p>
                                        <p x-text="school.city"></p>
                                    </div>
                                    <div
                                        class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                                        <x-lucide-chevron-right class="w-6 h-6" />
                                    </div>
                                </div>
                            </a>
                        </template>

                        <div class="flex justify-center gap-2">
                            <button @click="prevPage" :disabled="currentPage === 1"
                                :class="currentPage === 1 ? 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed' : 'bg-primary-500 text-white hover:bg-primary-600'"
                                class="flex-1 rounded p-2 transition">Previous</button>
                            <button @click="nextPage" :disabled="currentPage >= totalPages"
                                :class="currentPage >= totalPages ? 'bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-not-allowed' : 'bg-primary-500 text-white hover:bg-primary-600'"
                                class="flex-1 rounded p-2 transition">Next</button>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>

</x-website-layout>
