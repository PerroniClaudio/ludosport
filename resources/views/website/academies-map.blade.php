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
            v: "weekly"
        });
    </script>

    <div class="grid grid-cols-6 h-screen" x-data="mapsearcher">
        <div class="flex flex-col gap-2 col-span-2">
            <div class="flex items-center gap-2 p-2">
                <input type="text" placeholder="City/Zip Code" id="search"
                    class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
                    x-model="search" @input="searchChanged">
                <button class="bg-primary-500 text-white rounded p-2">
                    <x-lucide-search class="w-6 h-6" />
                </button>
            </div>

            <div class="flex flex-col gap-2 p-2">
                <template x-for="academy in results" :key="academy.id">
                    <div class="bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2"
                        @click="zoomToMarker(academy.id)">
                        <div class="flex flex-col gap-1">
                            <h1 class="font-bold dark:text-background-100" x-text="academy.name"></h1>
                            <p x-text="academy.address"></p>
                            <p x-text="academy.city"></p>
                        </div>
                        <div class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                            <x-lucide-chevron-right class="w-6 h-6" />
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="col-span-4">
            <div id="google-map" class="h-full w-full"></div>
        </div>
    </div>

</x-website-layout>
