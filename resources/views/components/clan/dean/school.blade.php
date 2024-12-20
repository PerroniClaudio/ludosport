@props([
    'selectedSchoolId' => auth()->user()->primarySchool()->id ?? '0',
    'selectedSchool' => auth()->user()->primarySchool()->name ?? 'Select a school',
])


<div x-data="{
    selectedSchoolId: '{{ $selectedSchoolId }}',
    selectedSchool: '{{ addslashes($selectedSchool) }}',
    availableSchools: [],
    paginatedSchools: [],
    currentPage: 1,
    totalPages: 1,
    getavailableSchools: function() {
        data = [{{ auth()->user()->primarySchool() }}];
        this.availableSchools = data;
        this.paginatedSchools = this.availableSchools.slice(0, 5);
        this.totalPages = Math.ceil(this.availableSchools.length / 5);
    },
    searchavailableSchools: function(event) {
    
    },
    goToPage: function(page) {
        if (page < 1 || page > this.totalPages) {
            return;
        }

        this.currentPage = page;
        this.paginatedSchools = this.availableSchools.slice((page - 1) * 5, page * 5);
    },
    init: function() {
        this.getavailableSchools();

    }
}">


    <x-input-label for="academy" value="{{ __('users.school') }}" />
    <div class="flex items-center gap-2">
        <input type="hidden" name="school_id" x-model="selectedSchoolId">
        <x-text-input disabled name="School" class="flex-1" type="text" x-model="selectedSchool" />
        {{-- Si è deciso di non modificare la scuola di appartenenza per evitare problemi con le associazioni degli atleti eventualmente presenti --}}
        {{-- <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
            x-on:click.prevent="$dispatch('open-modal', 'selected-school-modal')">
            <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
        </div> --}}
    </div>

    {{-- <x-modal name="selected-school-modal" :show="$errors->userId->isNotEmpty()" focusable>
        <div class="p-6 flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('clan.select_school') }}
                </h2>
                <div>
                    <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300 cursor-pointer"
                        x-on:click="$dispatch('close-modal', 'selected-school-modal')" />
                </div>
            </div>
            <div class="mt-4">
                <div
                    class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative min-h-[600px] flex flex-col justify-between max-h-[80vh]">

                    <div class="flex justify-between items-center p-6">
                        <div class="flex items-center justify-end w-full">
                            <x-text-input type="text" x-on:input="searchavailableSchools(event);"
                                placeholder="Search..."
                                class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                        </div>
                    </div>
                    <table
                        class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">

                        <thead>
                            <tr class="text-left">
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                    ID</th>
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                    Name</th>
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                    Academy</th>
                                <th
                                    class="bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 px-6 py-3 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                    Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <template x-if="paginatedSchools.length === 0">
                                <tr>
                                    <td colspan="100%"
                                        class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                        No records found
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(school, index) in paginatedSchools" :key="'school-' + index">
                                <tr class="hover:bg-background-200 dark:hover:bg-background-900 cursor-pointer"
                                    x-on:click="selectedSchoolId = school.id; selectedSchool = school.name;">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                        x-text="school.id" />
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                        x-text="school.name" />
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                        x-text="school.academy" />
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300">
                                        <x-primary-button type="button"
                                            x-on:click="selectedSchool = school.name; selectedSchoolId = school.id;$dispatch('close-modal', 'selected-school-modal');">{{ __('users.select') }}</x-primary-button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div class="flex items-center mb-4 mr-4">
                        <div class="flex-1">

                        </div>
                        <div class="flex justify-between items-center">
                            <button type="button" x-on:click="goToPage(1)" class="mr-2"
                                x-bind:disabled="currentPage === 1">
                                <x-lucide-chevron-first class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                            </button>
                            <button type="button" x-on:click="goToPage(currentPage - 1)" class="mr-2"
                                x-bind:disabled="currentPage === 1"
                                :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }">
                                <x-lucide-chevron-left class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                            </button>
                            <p class="text-sm text-background-500 dark:text-background-300">Page <span
                                    x-text="currentPage"></span> of
                                <span x-text="totalPages"></span>
                            </p>
                            <button type="button" x-on:click="goToPage(currentPage + 1)" class="ml-2"
                                x-bind:disabled="currentPage === totalPages"
                                :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }">
                                <x-lucide-chevron-right class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                            </button>
                            <button type="button" x-on:click="goToPage(totalPages)" class="ml-2"
                                x-bind:disabled="currentPage === totalPages">
                                <x-lucide-chevron-last class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-modal> --}}

</div>
