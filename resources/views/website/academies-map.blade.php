<x-website-layout>

    <script>
        (g => {
            var h, a, k, p = "The Google Maps JavaScript API",
                c = "google",
                l = "importLibrary",
                q = "__ib__",
                m = document,
                b = window;
            b = b[c] || (b[c] = {});
            var d = b.maps || (b.maps = {}),
                r = new Set,
                e = new URLSearchParams,
                u = () => h || (h = new Promise(async (f, n) => {
                    await (a = m.createElement("script"));
                    e.set("libraries", [...r] + "");
                    for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                    e.set("callback", c + ".maps." + q);
                    a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                    d[q] = f;
                    a.onerror = () => h = n(Error(p + " could not load."));
                    a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                    m.head.append(a)
                }));
            d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() =>
                d[l](f, ...n))
        })
        ({
            key: "{{ env('MAPS_GOOGLE_MAPS_ACCESS_TOKEN') }}",
            v: "weekly",

        });
    </script>

    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.academies_map') }}
            </h1>

            <p class="text-background-800 dark:text-background-200 text-justify">{{ __('website.academies_map_text') }}
            </p>

            <div class="flex flex-col gap-4 rounded  min-h-[60vh]  mt-8" x-data="mapsearcher({{ $schools_json }})" x-init="$watch('nationFilter', (value) => fiterByNation(value))">
                <div>
                    <div id="google-map" class="h-[600px] w-full"></div>
                </div>
                <div class="flex flex-col gap-2">

                    <div class="w-full p-2">
                        <x-form.select name="country" label="{{ __('website.academies_map_nations') }}"
                            x-model="nationFilter" shouldHaveEmptyOption="true" :options="$nations" />
                    </div>

                    <div class="flex items-center gap-2 p-2">
                        <input type="text" placeholder="City/Zip Code" id="search"
                            class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
                            x-model="search" @input="searchChanged">
                        <button class="bg-primary-500 text-white rounded p-2">
                            <x-lucide-search class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="flex flex-col gap-2 p-2">
                        <template x-for="school in paginatedResults" :key="school.id">
                            <a x-bind:href="'{{ env('APP_URL') }}/school-profile/' + school.slug">
                                <div
                                    class="bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2">
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
                            <button x-show="currentPage > 1" @click="prevPage"
                                class="flex-1 bg-primary-500 text-white rounded p-2">Previous</button>
                            <button x-show="currentPage < totalPages" @click="nextPage"
                                class="flex-1 bg-primary-500 text-white rounded p-2">Next</button>
                        </div>
                    </div>
                </div>

            </div>

        </section>
    </div>

</x-website-layout>
