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

            <div class="flex flex-col lg:grid lg:grid-cols-12 gap-4 rounded  min-h-[60vh]  mt-8" x-load
                x-data="rankingschart" x-init="$watch('nationFilter', (value) => fiterByNation(value))">
                <div class="flex flex-col gap-2 col-span-3">
                    <!-- Events -->

                    <div class="w-full">
                        <x-form.select name="country" label="{{ __('website.academies_map_nations') }}"
                            x-model="nationFilter" shouldHaveEmptyOption="false" :optgroups="$continents" />
                    </div>

                    <div class="hidden lg:flex flex-col gap-2">

                        <div :class="{
                            'bg-primary-500 text-white rounded dark:bg-background-800 dark:text-background-300 p-4 flex flex-row justify-between gap-2 cursor-pointer': selectedEvent ===
                                0,
                            'bg-white dark:bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2 cursor-pointer': selectedEvent !==
                                0
                        }"
                            data-id="0" @click="getGeneralRankings()">

                            <span x-show="nationFilter == ''">{{ __('General rank') }}</span>
                            <span x-show="nationFilter != ''" x-text="'National Rankings - '+nation.name"></span>
                            <div
                                class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                                <x-lucide-chevron-right class="w-6 h-6" />
                            </div>
                        </div>

                        <template x-for="event in events" :key="event.id">
                            <div :class="{
                                'bg-primary-500 text-white': selectedEvent === event.id,
                                'bg-white dark:bg-background-800': selectedEvent !== event.id
                            }"
                                class="bg-white dark:bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2 cursor-pointer"
                                data-id="0" @click="getDataForEvent(event.id); eventName = event.name">
                                <span x-text="event.name"></span>
                                <div
                                    class="flex flex-col justify-center align-center cursor-pointer hover:text-primary-500">
                                    <x-lucide-chevron-right class="w-6 h-6" />
                                </div>
                            </div>
                        </template>

                        <div x-on:click="resetToGeneralRankings()" x-show="nationFilter != ''"
                            class="bg-white dark:bg-background-800 rounded dark:text-background-300 p-4 flex flex-row justify-between gap-2 cursor-pointer">
                            <span>{{ __('Reset filter') }}</span>
                        </div>

                        <div class="flex items-center justify-center mt-8">
                            <img src="/logo-saber" alt="" class="h-64">
                        </div>
                    </div>
                </div>
                <div class="col-span-9">

                    <h1 class="font-bold tracking-tighter text-4xl pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300"
                        x-text="eventName"></h1>
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <div
                                class="overflow-x-auto bg-white dark:bg-background-800 rounded-lg shadow overflow-y-auto relative   w-full">
                                <div>
                                    <p x-text="rows.count"></p>

                                    <div class=" bg-white dark:bg-background-900 rounded-lg shadow  relative">
                                        <div class="flex justify-between items-center p-6">
                                            <div class="flex items-center justify-end w-full">
                                                <x-text-input type="text" x-on:input="searchByValue($event)"
                                                    placeholder="Search..."
                                                    class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                            </div>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table
                                                class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">

                                                <thead>
                                                    <tr class="text-left">


                                                        <template x-for="(column, index) in columns">
                                                            <th :class="`${column.columnClasses}`"
                                                                class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 ">
                                                                <div class="flex justify-between items-center"
                                                                    x-on:click="sort(index)">
                                                                    <p class="font-bold tracking-wider uppercase text-xs truncate"
                                                                        x-text="column.name"></p>
                                                                    <x-lucide-arrow-down-up
                                                                        class="w-4 h-4 text-primary-500 dark:text-primary-400 cursor-pointer hover:opacity-70" />
                                                                </div>
                                                            </th>
                                                        </template>


                                                    </tr>
                                                </thead>

                                                <tbody>

                                                    <template x-if="rows.length === 0">
                                                        <tr>
                                                            <td colspan="100%"
                                                                class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                                                No records found
                                                            </td>
                                                        </tr>
                                                    </template>

                                                    <template x-for="(row, rowIndex) in paginatedRows"
                                                        :key="'row-' + rowIndex">
                                                        <tr
                                                            class="bg-white dark:bg-background-900 hover:bg-background-50 hover:dark:bg-background-800 cursor-pointer">
                                                            <template x-for="(column, columnIndex) in columns"
                                                                :key="'column-' + columnIndex">
                                                                <td :class="`${column.rowClasses}`"
                                                                    class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap cursor-pointer bg-transparent">
                                                                    <div class="flex items-center gap-1">
                                                                        <div x-text="`${row[column.field]}`"
                                                                            class="truncate">
                                                                        </div>
                                                                        <template
                                                                            x-if="column.field === 'name' && (row.battle_name !== null) && (row.battle_name !== '')">
                                                                            <a
                                                                                x-bind:href="'/website-users/' + row.battle_name">
                                                                                <x-lucide-arrow-right
                                                                                    class="w-4 h-4 text-primary-500 dark:text-primary-400 cursor-pointer hover:opacity-70" />
                                                                            </a>
                                                                        </template>
                                                                        <template
                                                                            x-if="column.field === 'school' && (row.school_slug !== null) && (row.school_slug !== '')">
                                                                            <a
                                                                                x-bind:href="'/school-profile/' + row.school_slug">
                                                                                <x-lucide-arrow-right
                                                                                    class="w-4 h-4 text-primary-500 dark:text-primary-400 cursor-pointer hover:opacity-70" />
                                                                            </a>
                                                                        </template>


                                                                    </div>
                                                                </td>
                                                            </template>
                                                        </tr>
                                                    </template>

                                                </tbody>

                                            </table>
                                        </div>
                                        <div class="flex justify-between items-center p-6">
                                            <div class="flex items-center justify-end w-full">

                                                <div class="flex items-center">
                                                    <button type="button" x-on:click="page = 1" class="mr-2"
                                                        x-bind:disabled="page === 1">
                                                        <x-lucide-chevron-first
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                                    </button>
                                                    <button type="button" x-on:click="page = page - 1" class="mr-2"
                                                        x-bind:disabled="page === 1"
                                                        :class="{ 'opacity-50 cursor-not-allowed': page === 1 }">
                                                        <x-lucide-chevron-left
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                                    </button>
                                                    </button>
                                                    <p class="text-sm text-background-500 dark:text-background-300">Page
                                                        <span x-text="page"></span> of
                                                        <span x-text="totalPages()"></span>
                                                    </p>
                                                    <button type="button" x-on:click="page = page + 1" class="ml-2"
                                                        :class="{ 'opacity-50 cursor-not-allowed': page === totalPages() }"
                                                        x-bind:disabled="page === totalPages()">
                                                        <x-lucide-chevron-right
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                                    </button>
                                                    <button type="button" x-on:click="page = totalPages()"
                                                        class="ml-2" x-bind:disabled="page === totalPages()">
                                                        <x-lucide-chevron-last
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
        </section>
    </div>
</x-website-layout>
