<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('users.filter_title') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{
            availableAcademies: {{ collect($academies) }},
            paginatedAcademies: [],
            selectedAcademies: [],
            paginatedselectedAcademies: [],
            selectedAcademiesJson: [],
            currentAcademyPage: 1,
            totalAcademyPages: 1,
            searchavailableAcademies: function(event) {
                let query = event.target.value.toLowerCase();
                this.paginatedAcademies = this.availableAcademies.filter(function(item) {
                    return item.name.toLowerCase().includes(query);
                });
            },
            addAcademy: function(id) {
                let academy = this.availableAcademies.find(function(item) {
                    return item.id === id;
                });
                this.selectedAcademies.push(academy);
                this.availableAcademies = this.availableAcademies.filter(function(item) {
                    return item.id !== id;
                });
                this.availableAcademies.sort(function(a, b) {
                    return a.id - b.id;
                });
                this.paginatedAcademies = this.availableAcademies.slice((this.currentAcademyPage - 1) * 10, this.currentAcademyPage * 10);
                this.paginatedselectedAcademies = this.selectedAcademies;
                this.selectedAcademiesJson = JSON.stringify(this.selectedAcademies.map(function(item) {
                    return item.id;
                }));
        
                this.getAvailableSchools()
        
            },
            removeAcademy: function(id) {
                let academy = this.selectedAcademies.find(function(item) {
                    return item.id === id;
                });
                this.availableAcademies.push(academy);
                this.selectedAcademies = this.selectedAcademies.filter(function(item) {
                    return item.id !== id;
                });
        
                this.availableAcademies.sort(function(a, b) {
                    return a.id - b.id;
                });
        
                this.paginatedAcademies = this.availableAcademies.slice((this.currentAcademyPage - 1) * 10, this.currentAcademyPage * 10);
                this.paginatedselectedAcademies = this.selectedAcademies;
        
                this.getAvailableSchools()
            },
            goToAcademyPage: function(page) {
                this.currentAcademyPage = page;
                this.paginatedAcademies = this.availableAcademies.slice((page - 1) * 10, page * 10);
            },
        
            availableSchools: [],
            paginatedSchools: [],
            selectedSchools: [],
            paginatedselectedSchools: [],
            selectedSchoolsJson: [],
            currentSchoolPage: 1,
            totalSchoolPages: 1,
            hasSelectedAllSchools: false,
            searchavailableSchools: function(event) {
                let query = event.target.value.toLowerCase();
                this.paginatedSchools = this.availableSchools.filter(function(item) {
                    return item.name.toLowerCase().includes(query);
                });
            },
            addSchool: function(id) {
                let school = this.availableSchools.find(function(item) {
                    return item.id === id;
                });
                this.selectedSchools.push(school);
                this.availableSchools = this.availableSchools.filter(function(item) {
                    return item.id !== id;
                });
                this.availableSchools.sort(function(a, b) {
                    return a.id - b.id;
                });
                this.paginatedSchools = this.availableSchools.slice((this.currentSchoolPage - 1) * 10, this.currentSchoolPage * 10);
                this.paginatedselectedSchools = this.selectedSchools;
                this.selectedSchoolsJson = JSON.stringify(this.selectedSchools.map(function(item) {
                    return item.id;
                }));
        
                this.getAvailableCourses()
        
                if (this.selectedSchools.length === this.availableSchools.length) {
                    this.hasSelectedAllSchools = true;
                }
            },
            removeSchool: function(id) {
                let school = this.selectedSchools.find(function(item) {
                    return item.id === id;
                });
                this.availableSchools.push(school);
                this.selectedSchools = this.selectedSchools.filter(function(item) {
                    return item.id !== id;
                });
        
                this.availableSchools.sort(function(a, b) {
                    return a.id - b.id;
                });
        
                this.paginatedSchools = this.availableSchools.slice((this.currentSchoolPage - 1) * 10, this.currentSchoolPage * 10);
                this.paginatedselectedSchools = this.selectedSchools;
        
                this.getAvailableCourses()
        
                this.hasSelectedAllSchools = false;
            },
            goToSchoolPage: function(page) {
                this.currentSchoolPage = page;
                this.paginatedSchools = this.availableSchools.slice((page - 1) * 10, page * 10);
            },
            getAvailableSchools: function() {
        
                if (this.selectedAcademies.length === 0) {
                    this.availableSchools = [];
                    this.paginatedSchools = [];
                    this.totalSchoolPages = 1;
                    return;
                }
        
                let academies = this.selectedAcademies.map(function(item) {
                    return item.id;
                });
        
                const params = new URLSearchParams({
                    academies: JSON.stringify(academies)
                });
        
                fetch(`/manager/schools/academy?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        this.availableSchools = data;
                        this.paginatedSchools = this.availableSchools.slice(0, 10);
                        this.totalSchoolPages = Math.ceil(this.availableSchools.length / 10);
                    });
        
            },
        
            availableCourses: [],
            paginatedCourses: [],
            selectedCourses: [],
            paginatedselectedCourses: [],
            selectedCoursesJson: [],
            currentCoursePage: 1,
            totalCoursePages: 1,
            hasSelectedAllCourses: false,
            searchavailableCourses: function(event) {
                let query = event.target.value.toLowerCase();
                this.paginatedCourses = this.availableCourses.filter(function(item) {
                    return item.name.toLowerCase().includes(query);
                });
            },
            addCourse: function(id) {
                let course = this.availableCourses.find(function(item) {
                    return item.id === id;
                });
                this.selectedCourses.push(course);
                this.availableCourses = this.availableCourses.filter(function(item) {
                    return item.id !== id;
                });
                this.availableCourses.sort(function(a, b) {
                    return a.id - b.id;
                });
                this.paginatedCourses = this.availableCourses.slice((this.currentCoursePage - 1) * 10, this.currentCoursePage * 10);
                this.paginatedselectedCourses = this.selectedCourses;
        
                this.selectedCoursesJson = JSON.stringify(this.selectedCourses.map(function(item) {
                    return item.id;
                }));
        
        
                if (this.selectedCourses.length === this.availableCourses.length) {
                    this.hasSelectedAllCourses = true;
                }
        
            },
            removeCourse: function(id) {
                let course = this.selectedCourses.find(function(item) {
                    return item.id === id;
                });
                this.availableCourses.push(course);
                this.selectedCourses = this.selectedCourses.filter(function(item) {
                    return item.id !== id;
                });
        
                this.availableCourses.sort(function(a, b) {
                    return a.id - b.id;
                });
        
                this.paginatedCourses = this.availableCourses.slice((this.currentCoursePage - 1) * 10, this.currentCoursePage * 10);
                this.paginatedselectedCourses = this.selectedCourses;
        
                this.hasSelectedAllCourses = false;
            },
            goToCoursePage: function(page) {
                this.currentCoursePage = page;
                this.paginatedCourses = this.availableCourses.slice((page - 1) * 10, page * 10);
            },
            getAvailableCourses: function() {
        
                if (this.selectedSchools.length === 0) {
                    this.availableCourses = [];
                    this.paginatedCourses = [];
                    this.totalCoursePages = 1;
                    return;
                }
        
                let schools = this.selectedSchools.map(function(item) {
                    return item.id;
                });
        
                const params = new URLSearchParams({
                    schools: JSON.stringify(schools)
                });
        
                fetch(`/manager/courses/school?${params}`)
                    .then(response => response.json())
                    .then(data => {
                        this.availableCourses = data;
                        this.paginatedCourses = this.availableCourses.slice(0, 10);
                        this.totalCoursePages = Math.ceil(this.availableCourses.length / 10);
                    });
        
            },
        
            init() {
                this.paginatedAcademies = this.availableAcademies.slice(0, 10);
                this.paginatedselectedAcademies = this.selectedAcademies.slice(0, 10);
                this.totalAcademyPages = Math.ceil(this.availableAcademies.length / 10);
            }
        }">
            <form action="{{ route('manager.users.filter.result') }}" method="GET" class="flex flex-col gap-2">



                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <h3 class="text-background-800 dark:text-background-200 text-md">
                            {{ __('users.filter_by_subscription_year') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <div class="flex flex-col w-1/2">
                            <x-form.input name="year" label="First subscription year" type="number"
                                placeholder="{{ date('Y') }}" />
                        </div>

                    </div>
                </div>
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100">
                        <h3 class="text-background-800 dark:text-background-200 text-md">
                            {{ __('users.filter_by_creation_date') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <div class="flex w-full items-center gap-2">
                            <div class="flex-1">
                                <x-form.input name="from" label="From" type="date"
                                    placeholder="{{ date('Y') }}" />
                            </div>

                            <div class="flex-1">
                                <x-form.input name="to" label="To" type="date"
                                    placeholder="{{ date('Y') }}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100 flex flex-col gap-2">
                        <h3 class="text-background-800 dark:text-background-200 text-md">
                            {{ __('users.filter_by_fee') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                        <x-form.checkbox id="fee" name="fee" label="Paid" />
                    </div>
                </div>

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-background-900 dark:text-background-100 flex flex-col gap-2">
                        <h3 class="text-background-800 dark:text-background-200 text-md">
                            {{ __('users.filter_by_academy') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>


                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-white dark:bg-background-900 p-4 rounded">
                                <div class="flex justify-between gap-2 items-center">
                                    <div class="flex-1">
                                        <h4 class="text-background-800 dark:text-background-200 text-lg">
                                            {{ __('exports.available_academies') }}
                                        </h4>
                                    </div>
                                    <div>
                                        <x-text-input type="text" x-on:input="searchavailableAcademies(event);"
                                            placeholder="Search..."
                                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                    </div>
                                </div>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                                <table
                                    class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                                    <thead>
                                        <tr class="">
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('academies.academy') }}</th>
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('academies.nation') }}</th>
                                            <th
                                                class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('users.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, index) in paginatedAcademies">
                                            <tr>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.name"></td>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.nation.name"></td>
                                                <td
                                                    class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                                    <button type="button" @click="addAcademy(row.id)">
                                                        <x-lucide-plus
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>


                                <div class="flex items-center">
                                    <div class="flex-1">

                                    </div>
                                    <div class="flex justify-between items-center">
                                        <button type="button" x-on:click="goToAcademyPage(1)" class="mr-2"
                                            x-bind:disabled="currentAcademyPage === 1">
                                            <x-lucide-chevron-first
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <button type="button" x-on:click="goToAcademyPage(currentAcademyPage - 1)"
                                            class="mr-2" x-bind:disabled="currentAcademyPage === 1"
                                            :class="{ 'opacity-50 cursor-not-allowed': currentAcademyPage === 1 }">
                                            <x-lucide-chevron-left
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <p class="text-sm text-background-500 dark:text-background-300">Page <span
                                                x-text="currentAcademyPage"></span> of
                                            <span x-text="totalAcademyPages"></span>
                                        </p>
                                        <button type="button" x-on:click="goToAcademyPage(currentAcademyPage + 1)"
                                            class="ml-2" x-bind:disabled="currentAcademyPage === totalAcademyPages"
                                            :class="{
                                                'opacity-50 cursor-not-allowed': currentAcademyPage ===
                                                    totalAcademyPages
                                            }">
                                            <x-lucide-chevron-right
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <button type="button" x-on:click="goToAcademyPage(totalAcademyPages)"
                                            class="ml-2" x-bind:disabled="currentAcademyPage === totalAcademyPages">
                                            <x-lucide-chevron-last
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <div class="bg-white dark:bg-background-900 p-4 rounded">
                                <div class="flex justify-between gap-2 items-center">
                                    <div class="flex-1">
                                        <h4 class="text-background-800 dark:text-background-200 text-lg">
                                            {{ __('exports.selected_academies') }}
                                        </h4>

                                    </div>

                                    <div>
                                        <x-text-input type="text" x-on:input="searchavailableAcademies(event);"
                                            placeholder="Search..."
                                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                    </div>

                                </div>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                                <table
                                    class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                                    <thead>
                                        <tr class="">
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('clan.name') }}</th>
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('academies.nation') }}</th>
                                            <th
                                                class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('users.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, index) in paginatedselectedAcademies">
                                            <tr>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.name"></td>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.nation.name"></td>
                                                <td
                                                    class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                                    <button type="button" @click="removeAcademy(row.id)">
                                                        <x-lucide-minus
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>

                            </div>



                        </div>



                        <h3 class="text-background-800 dark:text-background-200 text-md">
                            {{ __('users.filter_by_school') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>


                        <div class="grid grid-cols-2 gap-2"
                            x-show="paginatedSchools.length > 0 || hasSelectedAllSchools">
                            <div class="bg-white dark:bg-background-900 p-4 rounded">
                                <div class="flex justify-between gap-2 items-center">
                                    <div class="flex-1">
                                        <h4 class="text-background-800 dark:text-background-200 text-lg">
                                            {{ __('exports.available_schools') }}
                                        </h4>
                                    </div>
                                    <div>
                                        <x-text-input type="text" x-on:input="searchavailableSchools(event);"
                                            placeholder="Search..."
                                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                    </div>
                                </div>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                                <table
                                    class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                                    <thead>
                                        <tr class="">
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('school.name') }}</th>
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('school.academy') }}</th>
                                            <th
                                                class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('users.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, index) in paginatedSchools">
                                            <tr>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.name"></td>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.academy"></td>
                                                <td
                                                    class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                                    <button type="button" @click="addSchool(row.id)">
                                                        <x-lucide-plus
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>


                                <div class="flex items-center">
                                    <div class="flex-1">

                                    </div>
                                    <div class="flex justify-between items-center">
                                        <button type="button" x-on:click="goToSchoolPage(1)" class="mr-2"
                                            x-bind:disabled="currentSchoolPage === 1">
                                            <x-lucide-chevron-first
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <button type="button" x-on:click="goToSchoolPage(currentSchoolPage - 1)"
                                            class="mr-2" x-bind:disabled="currentSchoolPage === 1"
                                            :class="{ 'opacity-50 cursor-not-allowed': currentSchoolPage === 1 }">
                                            <x-lucide-chevron-left
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <p class="text-sm text-background-500 dark:text-background-300">Page <span
                                                x-text="currentSchoolPage"></span> of
                                            <span x-text="totalSchoolPages"></span>
                                        </p>
                                        <button type="button" x-on:click="goToSchoolPage(currentSchoolPage + 1)"
                                            class="ml-2" x-bind:disabled="currentSchoolPage === totalSchoolPages"
                                            :class="{ 'opacity-50 cursor-not-allowed': currentSchoolPage === totalSchoolPages }">
                                            <x-lucide-chevron-right
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <button type="button" x-on:click="goToSchoolPage(totalSchoolPages)"
                                            class="ml-2" x-bind:disabled="currentSchoolPage === totalSchoolPages">
                                            <x-lucide-chevron-last
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <div class="bg-white dark:bg-background-900 p-4 rounded">
                                <div class="flex justify-between gap-2 items-center">
                                    <div class="flex-1">
                                        <h4 class="text-background-800 dark:text-background-200 text-lg">
                                            {{ __('exports.selected_schools') }}
                                        </h4>

                                    </div>

                                    <div>
                                        <x-text-input type="text" x-on:input="searchavailableSchools(event);"
                                            placeholder="Search..."
                                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                    </div>

                                </div>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                                <table
                                    class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                                    <thead>
                                        <tr class="">
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('school.name') }}</th>
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('school.academy') }}</th>
                                            <th
                                                class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('users.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, index) in paginatedselectedSchools">
                                            <tr>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.name"></td>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.academy"></td>
                                                <td
                                                    class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                                    <button type="button" @click="removeSchool(row.id)">
                                                        <x-lucide-minus
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>

                            </div>



                        </div>

                        <h3 class="text-background-800 dark:text-background-200 text-md">
                            {{ __('users.filter_by_course') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>


                        <div class="grid grid-cols-2 gap-2"
                            x-show="paginatedCourses.length > 0 || hasSelectedAllCourses">
                            <div class="bg-white dark:bg-background-900 p-4 rounded">
                                <div class="flex justify-between gap-2 items-center">
                                    <div class="flex-1">
                                        <h4 class="text-background-800 dark:text-background-200 text-lg">
                                            {{ __('exports.available_courses') }}
                                        </h4>
                                    </div>
                                    <div>
                                        <x-text-input type="text" x-on:input="searchAvailableCourses(event);"
                                            placeholder="Search..."
                                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                    </div>
                                </div>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                                <table
                                    class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                                    <thead>
                                        <tr class="">
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('clan.name') }}</th>
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('clan.school') }}</th>
                                            <th
                                                class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('users.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, index) in paginatedCourses">
                                            <tr>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.name"></td>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.school"></td>
                                                <td
                                                    class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                                    <button type="button" @click="addCourse(row.id)">
                                                        <x-lucide-plus
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>


                                <div class="flex items-center">
                                    <div class="flex-1">

                                    </div>
                                    <div class="flex justify-between items-center">
                                        <button type="button" x-on:click="goToCoursePage(1)" class="mr-2"
                                            x-bind:disabled="currentCoursePage === 1">
                                            <x-lucide-chevron-first
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <button type="button" x-on:click="goToCoursePage(currentCoursePage - 1)"
                                            class="mr-2" x-bind:disabled="currentCoursePage === 1"
                                            :class="{ 'opacity-50 cursor-not-allowed': currentCoursePage === 1 }">
                                            <x-lucide-chevron-left
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <p class="text-sm text-background-500 dark:text-background-300">Page <span
                                                x-text="currentCoursePage"></span> of
                                            <span x-text="totalCoursePages"></span>
                                        </p>
                                        <button type="button" x-on:click="goToCoursePage(currentCoursePage + 1)"
                                            class="ml-2" x-bind:disabled="currentCoursePage === totalCoursePages"
                                            :class="{ 'opacity-50 cursor-not-allowed': currentCoursePage === totalCoursePages }">
                                            <x-lucide-chevron-right
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                        <button type="button" x-on:click="goToCoursePage(totalCoursePages)"
                                            class="ml-2" x-bind:disabled="currentCoursePage === totalCoursePages">
                                            <x-lucide-chevron-last
                                                class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                        </button>
                                    </div>
                                </div>

                            </div>

                            <div class="bg-white dark:bg-background-900 p-4 rounded">
                                <div class="flex justify-between gap-2 items-center">
                                    <div class="flex-1">
                                        <h4 class="text-background-800 dark:text-background-200 text-lg">
                                            {{ __('exports.selected_courses') }}
                                        </h4>

                                    </div>

                                    <div>
                                        <x-text-input type="text" x-on:input="searchAvailableCourses(event);"
                                            placeholder="Search..."
                                            class="border border-background-100 dark:border-background-700 text-background-500 dark:text-background-300 rounded-lg p-2" />
                                    </div>

                                </div>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                                <table
                                    class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                                    <thead>
                                        <tr class="">
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('clan.name') }}</th>
                                            <th
                                                class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('clan.school') }}</th>
                                            <th
                                                class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                                                {{ __('users.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(row, index) in paginatedselectedCourses">
                                            <tr>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.name"></td>
                                                <td class="text-background-500 dark:text-background-300 text-sm"
                                                    x-text="row.school"></td>
                                                <td
                                                    class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                                    <button type="button" @click="removeCourse(row.id)">
                                                        <x-lucide-minus
                                                            class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>

                            </div>



                        </div>

                        <input type="hidden" name="selectedAcademiesJson" x-model="selectedAcademiesJson">
                        <input type="hidden" name="selectedSchoolsJson" x-model="selectedSchoolsJson">
                        <input type="hidden" name="selectedCoursesJson" x-model="selectedCoursesJson">

                    </div>
                </div>

                <div class="flex justify-end">
                    <x-primary-button>
                        {{ __('users.filter_title') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
