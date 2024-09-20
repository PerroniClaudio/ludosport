<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.rankings') }}
            </h1>

            <p class="text-background-800 dark:text-background-200 text-justify">{{ __('website.rankings_text') }}
            </p>

            <p class="text-background-800 dark:text-background-200 text-justify">You can distinguish between <span
                    class="text-primary-500 mr-1">Arena Points</span> and
                <span class="text-secondary-500 ml-1">Style Points</span> with their colour.
            </p>

            <div class="flex flex-col lg:grid lg:grid-cols-12 gap-4 rounded  min-h-[60vh]  mt-8" x-data="rankingschart"
                x-init="$watch('nationFilter', (value) => fiterByNation(value))">
                <div class="flex flex-col gap-2 col-span-3">
                    <!-- Events -->

                    <div class="w-full">
                        <x-form.select name="country" label="{{ __('website.academies_map_nations') }}"
                            x-model="nationFilter" shouldHaveEmptyOption="false" :optgroups="$continents" />
                    </div>

                    <div class="hidden lg:flex flex-col gap-2">

                        <div class="bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2 cursor-pointer"
                            data-id="0" @click="getGeneralRankings()">

                            <span>{{ __('General rank') }}</span>
                            <div
                                class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                                <x-lucide-chevron-right class="w-6 h-6" />
                            </div>
                        </div>

                        <template x-for="event in events" :key="event.id">
                            <div class="bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2 cursor-pointer"
                                data-id="0" @click="getDataForEvent(event.id); eventName = event.name">
                                <span x-text="event.name"></span>
                                <div
                                    class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                                    <x-lucide-chevron-right class="w-6 h-6" />
                                </div>
                            </div>
                        </template>

                        <div class="flex items-center justify-center mt-8">
                            <img src="/logo-saber" alt="" class="h-64">
                        </div>
                    </div>
                </div>
                <div class="col-span-9">

                    <h1 class="font-bold tracking-tighter text-4xl pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300"
                        x-text="eventName"></h1>


                    <!--

                    <div class="flex flex-col gap-4 px-8 max-h-[80vh] overflow-y-scroll">
                        <template x-for="athlete in athletesData" :key="athlete.id">
                            <div class="bg-background-800 rounded dark:text-background-300 p-4">
                                <div class="flex justify-between gap-1 items-center">
                                    <div class="grid grid-cols-4 gap-1 items-center">
                                        <div>
                                            <h1 class="font-bold dark:text-background-100" x-text="athlete.name"></h1>
                                        </div>
                                        <div>
                                            <h1 class="font-bold dark:text-background-100" x-text="athlete.academy">
                                            </h1>
                                        </div>
                                        <div>
                                            <h1 class="font-bold dark:text-background-100" x-text="athlete.school"></h1>
                                        </div>
                                        <div>
                                            <h1 class="font-bold dark:text-background-100" x-text="athlete.nation"></h1>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <p class="text-primary-500" x-text="athlete.war_points"></p>
                                        <p class="text-secondary-500" x-text="athlete.style_points"></p>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                    -->

                    <div
                        class="mb-5 overflow-x-auto bg-white dark:bg-background-800 rounded-lg shadow overflow-y-auto relative p-2 w-full">

                        <table
                            class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative rounded">

                            <thead>
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate rounded">
                                    <span>{{ __('users.name') }}</span>
                                </th>
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                    <span>{{ __('users.academy') }}</span>
                                </th>
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                    <span>{{ __('users.school') }}</span>
                                </th>
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                    <span>{{ __('academies.nation') }}</span>
                                </th>
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                    <span>{{ __('website.events_general_points') }}</span>
                                </th>

                            </thead>

                            <tbody>

                                <template x-for="athlete in athletesData" :key="athlete.id">
                                    <tr @click="window.location.href = `{{ env('APP_URL') }}/website-users/${athlete.battle_name}`"
                                        class="cursor-pointer hover:bg-background-100 dark:hover:bg-background-800">
                                        <td
                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                            <h1 class="font-bold dark:text-background-100" x-text="athlete.name">
                                            </h1>
                                        </td>
                                        <td
                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                            <h1 class="font-bold dark:text-background-100 text-xs"
                                                x-text="athlete.academy">
                                            </h1>
                                        </td>
                                        <td
                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                            <h1 class="font-bold dark:text-background-100 text-xs"
                                                x-text="athlete.school"></h1>
                                        </td>
                                        <td
                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                            <h1 class="font-bold dark:text-background-100 text-xs"
                                                x-text="athlete.nation"></h1>
                                        </td>
                                        <td
                                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                            <span class="text-primary-500 mr-1"
                                                x-text="athlete.war_points"></span>|<span
                                                class="text-secondary-500 ml-1" x-text="athlete.style_points"></span>
                                        </td>
                                    </tr>
                                </template>

                            </tbody>

                        </table>

                    </div>

                </div>
        </section>
    </div>
</x-website-layout>
