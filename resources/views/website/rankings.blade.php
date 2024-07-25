<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.rankings') }}
            </h1>

            <p class="text-background-800 dark:text-background-200 text-justify">{{ __('website.rankings_text') }}
            </p>

            <div class="grid grid-cols-6 rounded  min-h-[60vh]  mt-8" x-data="rankingschart">
                <div class="flex flex-col gap-2 col-span-2">
                    <!-- Events -->
                    <div class="bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2"
                        data-id="0" @click="showRanking(0)">

                        <span>{{ __('General rank') }}</span>
                        <div class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                            <x-lucide-chevron-right class="w-6 h-6" />
                        </div>
                    </div>

                    <template x-for="event in events" :key="event.id">
                        <div class="bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2"
                            data-id="0" @click="showRanking(event.id)">
                            <span x-text="event.name"></span>
                            <div
                                class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                                <x-lucide-chevron-right class="w-6 h-6" />
                            </div>
                        </div>
                    </template>
                </div>
                <div class="col-span-4">
                    <!-- Table -->
                </div>
            </div>
        </section>
    </div>
</x-website-layout>
