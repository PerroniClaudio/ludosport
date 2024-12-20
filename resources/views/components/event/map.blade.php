@props(['event' => $event])
@php
    $authRole = auth()->user()->getRole();
    $formRoute = $authRole === 'admin' ? 'events.save.location' : $authRole . '.events.save.location';
@endphp

<script>
    async function saveMapContent() {
        @if ($authRole === 'admin' || (!$event->is_approved && $authRole === 'rector'))
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

<div x-load x-data="googlemap('{{ $event->location }}')" x-ref="eventGoogleMapContainer"
    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
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

        @if ($authRole === 'admin' || (!$event->is_approved && $authRole === 'rector'))
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

        @if (Auth::user()->hasRole('admin') || (!$event->is_approved && ($authRole === 'rector')))
            <div class="col-span-2 flex items-center gap-2 mt-8">
                <div class="flex-1">
                    <x-primary-button type="button" class="w-full" @click="updateMap()">
                        <div class="flex flex-col items-center justify-center w-full"><x-lucide-search
                                class="w-5 h-5 text-white" /></div>
                    </x-primary-button>
                </div>
            </div>
        @endif
        </form>

        <div>
            <x-maps-google id="eventGoogleMap" style="height: 400px"></x-maps-google>
        </div>
    </div>
</div>
