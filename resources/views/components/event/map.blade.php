@props(['event' => $event])
@php
    $authRole = auth()->user()->getRole();
    $formRoute = $authRole === 'admin' ? 'events.save.location' : $authRole . '.events.save.location';
@endphp

<script>
    async function saveMapContent() {
        @if ($authRole === 'admin' || (!$event->is_approved && ($authRole === 'rector' || $authRole === 'manager')))
            const location = document.getElementById('eventLocationCoordinates').value;


            const city = document.getElementById('eventCity').value;
            const address = document.getElementById('eventAddress').value;
            const postalCode = document.getElementById('eventPostalCode').value;
            const country = document.getElementById('eventCountry').value;

            const formDataContent = new FormData();

            formDataContent.append('location', location);
            formDataContent.append('city', city);
            formDataContent.append('address', address);
            formDataContent.append('postal_code', postalCode);
            formDataContent.append('nation', country);
            formDataContent.append('shouldJson', true);


            return fetch(`{{ route($formRoute, $event->id) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formDataContent
                }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('check-save-map').classList.remove('hidden');
                        setTimeout(() => {
                            document.getElementById('check-save-map').classList.add('hidden');
                        }, 2000);
                    }
                })
        @else
            return Promise.resolve();
        @endif
    }
</script>

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8"
    x-data="{
        googleMapsReady: false,
        googleMapsAccepted: false,
        
        init() {
            this.checkGoogleMapsAccepted();
            
            // Ascolta i cambiamenti di localStorage
            window.addEventListener('storage', (e) => {
                if (e.key === 'policyChoices') {
                    console.log('[event-map] Preferences updated from another tab/window');
                    this.checkGoogleMapsAccepted();
                }
            });
            
            // Ascolta l'evento custom dal banner
            window.addEventListener('policyChoicesUpdated', (e) => {
                console.log('[event-map] Preferences updated from banner:', e.detail.choices);
                this.checkGoogleMapsAccepted();
            });
        },
        
        checkGoogleMapsAccepted() {
            const policyChoices = JSON.parse(localStorage.getItem('policyChoices') || '{}');
            const isAccepted = policyChoices.cookie_policy?.categories?.google_api === true;
            this.googleMapsAccepted = isAccepted;
            this.googleMapsReady = isAccepted;
            
            console.log('[event-map] Google APIs accepted:', isAccepted);
            
            // Se Google Maps è stato appena abilitato, reinizializza il component
            if (isAccepted) {
                this.reinitializeGoogleMaps();
            }
        },
        
        reinitializeGoogleMaps() {
            console.log('[event-map] Reinitializing Google Maps...');
            // Dispatch un evento custom che Alpine ascolterà
            const event = new CustomEvent('googleMapsEnabled', { detail: { timestamp: Date.now() } });
            window.dispatchEvent(event);
        }
    }" x-init="init()">
    <div class="flex items-center gap-1">
        <div class="flex items-center gap-1">
            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.location') }}</h3>
            <div class="hidden" id="check-save-map">
                <x-lucide-circle-check class="w-5 h-5 text-primary-500 dark:text-primary-500 cursor-pointer" />
            </div>
        </div>

        <div class='has-tooltip'>
            <span
                class='tooltip rounded shadow-lg p-1 bg-primary-500 text-white text-sm max-w-[800px] -mt-6 -translate-y-full'>{{ __('events.event_map_tooltip') }}</span>
            <x-lucide-info class="w-5 h-5 text-primary-500 dark:text-primary-500 cursor-pointer" />
        </div>

    </div>

    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <div class="grid grid-cols-2 gap-4">
        <!-- Form always visible -->
        <div x-load x-data="googlemap('{{ $event->location }}')" x-ref="eventGoogleMapContainer">
            @if ($authRole === 'admin' || (!$event->is_approved && ($authRole === 'rector' || $authRole === 'manager')))
                <form action="{{ route($formRoute, $event->id) }}" method="POST" class="h-96">
                @else
                    <form>
            @endif
            @csrf
            <div>
                <x-input-label value="City" />
                <input type="text" id="eventCity" name="city" x-model="city"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>
            <div>
                <x-input-label value="Address" />
                <input type="text" id="eventAddress" name="address" x-model="address"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>
            <div>
                <x-input-label value="Postal Code" />
                <input type="text" id="eventPostalCode" name="postal_code" x-model="postal_code"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>
            <div>
                <x-input-label value="Country" />
                <input type="text" id="eventCountry" name="nation" x-model="country"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>

            <input type="hidden" name="location" id="eventLocationCoordinates" x-model="location">

            @if (Auth::user()->hasRole('admin') || (!$event->is_approved && ($authRole === 'rector' || $authRole === 'manager')))
                <div class="col-span-2 flex items-center gap-2 mt-8">
                    <div class="flex-1">
                        <x-primary-button type="button" class="w-full" @click="updateMap()">
                            <div class="flex flex-col items-center justify-center w-full"><x-lucide-search
                                    class="w-5 h-5 text-white" /></div>
                        </x-primary-button>
                    </div>
                </div>
                
                <!-- Feedback section -->
                <div class="col-span-2">
                    <template x-if="searchStatus || searchMessage">
                        <div :class="[
                            'flex items-center gap-2 p-3 rounded-md text-sm',
                            searchStatus === 'success' ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200' : '',
                            searchStatus === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800/50' : '',
                            searchStatus === 'error' ? 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200' : ''
                        ]">
                            <!-- Loading state -->
                            <template x-if="searchStatus === 'loading'">
                                <div class="animate-spin" x-cloak>
                                    <x-lucide-loader class="w-4 h-4" />
                                </div>
                            </template>
                            
                            <!-- Success state -->
                            <template x-if="searchStatus === 'success'">
                                <x-lucide-circle-check class="w-4 h-4 flex-shrink-0" />
                            </template>
                            
                            <!-- Warning state -->
                            <template x-if="searchStatus === 'warning'">
                                <x-lucide-alert-circle class="w-4 h-4 flex-shrink-0" />
                            </template>
                            
                            <!-- Error state -->
                            <template x-if="searchStatus === 'error'">
                                <x-lucide-circle-x class="w-4 h-4 flex-shrink-0" />
                            </template>
                            
                            <span x-text="searchMessage" class="font-medium"></span>
                        </div>
                    </template>
                </div>
            @endif
            </form>
        </div>

        <!-- Map visible only if Google APIs loaded -->
        <template x-if="googleMapsReady">
            <div>
                <x-maps-google id="eventGoogleMap" style="height: 400px"></x-maps-google>
            </div>
        </template>
        
        <!-- Message if Google APIs not accepted -->
        <template x-if="!googleMapsAccepted">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/50 p-6 rounded-lg flex flex-col gap-4 items-start h-fit">
                <div>
                    <h3 class="text-base font-semibold text-yellow-800 dark:text-yellow-100 mb-2">
                        {{ __('website.cookies_google_apis_required') ?? 'Google APIs Required' }}
                    </h3>
                    <p class="text-yellow-700 dark:text-yellow-200 text-sm">
                        {{ __('website.cookies_google_apis_message') ?? 'To view the event location on the map, you need to enable Google APIs in your cookie preferences.' }}
                    </p>
                </div>
                <button @click="typeof window.openCookiePreferences === 'function' && window.openCookiePreferences()"
                    class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded hover:bg-primary-700 transition">
                    {{ __('website.cookies_manage_preferences') ?? 'Manage Preferences' }}
                </button>
            </div>
        </template>
    </div>
</div>
