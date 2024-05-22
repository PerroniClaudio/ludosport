@props(['event' => $event])

<div x-data="googlemap('{{ $event->location }}')" x-ref="eventGoogleMapContainer"
    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.location') }}</h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <div class="grid grid-cols-2 gap-4">
        <div class="h-96">
            <div>
                <x-input-label value="City" />
                <input type="text" id="eventCity" x-model="city"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>
            <div>
                <x-input-label value="Address" />
                <input type="text" id="eventAddress" x-model="address"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>
            <div>
                <x-input-label value="Postal Code" />
                <input type="text" id="eventPostalCode" x-model="postal_code"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>
            <div>
                <x-input-label value="Country" />
                <input type="text" id="eventCountry" x-model="country"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm" />
            </div>

            <div class="col-span-2 flex items-center gap-2 mt-8">
                <div class="flex-1">
                    <x-primary-button class="w-full" @click="updateMap()">
                        <div class="flex flex-col items-center justify-center w-full"><x-lucide-search
                                class="w-5 h-5 text-white" /></div>
                    </x-primary-button>
                </div>
                <div class="flex-1">
                    <form action="{{ route('technician.events.save.location', $event->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="location" x-model="location">
                        <x-primary-button class="w-full">
                            <div class="flex flex-col items-center justify-center w-full"><x-lucide-save
                                    class="w-5 h-5 text-white" /></div>
                        </x-primary-button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.preview_map') }}</h3>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <x-maps-google id="eventGoogleMap" style="height: 400px"></x-maps-google>
        </div>
    </div>
</div>
