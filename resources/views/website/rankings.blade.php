<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.rankings') }}
            </h1>

            <p class="text-background-800 dark:text-background-200 text-justify">{{ __('website.rankings_text') }}
            </p>

            <div class="grid grid-cols-6 rounded  min-h-[60vh]  mt-8" x-data="rankingschart" x-init="$watch('nationFilter', (value) => fiterByNation(value))">
                <div class="flex flex-col gap-2 col-span-2">
                    <!-- Events -->

                    <div class="w-full p-2">
                        <x-form.select name="country" label="{{ __('website.academies_map_nations') }}"
                            x-model="nationFilter" shouldHaveEmptyOption="false" :optgroups="$continents" />
                    </div>

                    <div class="bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2 cursor-pointer"
                        data-id="0" @click="getGeneralRankings()">

                        <span>{{ __('General rank') }}</span>
                        <div class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                            <x-lucide-chevron-right class="w-6 h-6" />
                        </div>
                    </div>

                    <template x-for="event in events" :key="event.id">
                        <div class="bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2 cursor-pointer""
                            data-id="0" @click="getDataForEvent(event.id); eventName = event.name">
                            <span x-text="event.name"></span>
                            <div
                                class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                                <x-lucide-chevron-right class="w-6 h-6" />
                            </div>
                        </div>
                    </template>
                </div>
                <div class="col-span-4">
                    <div class="px-8">
                        <h1 class="font-bold tracking-tighter text-4xl pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300"
                            x-text="eventName"></h1>
                    </div>

                    <!-- Table -->
                    <div class="flex flex-col gap-4 px-8 max-h-[80vh] overflow-y-scroll">
                        <template x-for="athlete in athletesData" :key="athlete.id">
                            <div class="bg-background-800 rounded dark:text-background-300 p-4">
                                <div class="flex justify-between gap-1 items-center">
                                    <div class="flex-1">
                                        <h1 class="font-bold dark:text-background-100" x-text="athlete.name"></h1>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-primary-500" x-text="athlete.war_points"></p>
                                        <p class="text-secondary-500" x-text="athlete.style_points"></p>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-website-layout>
