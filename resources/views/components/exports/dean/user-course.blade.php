<form action="{{ route('dean.exports.store') }}" method="POST" x-data="{
    filters: [],
    filtersJson: '[]',
    selectedCourses: [],
    availableCourses: [],
    paginatedCourses: [],
    paginatedSelectedCourses: [],
    currentPage: 1,
    totalPages: 1,
    usersType: '',
    isSubmitEnabled: false,
    getAvailableCourses: function() {
        fetch('/dean/courses/all')
            .then(response => response.json())
            .then(data => {
                this.availableCourses = data;
                this.paginatedCourses = this.availableCourses.slice(0, 10);
                this.totalPages = Math.ceil(this.availableCourses.length / 10);
            })
            .catch(error => {
                console.error(error);
            });
    },
    searchAvailableCourses: function(event) {

        if (event.target.value.length >= 3) {
            fetch('/dean/courses/search?search=' + event.target.value)
                .then(response => response.json())
                .then(data => {
                    this.availableCourses = data;
                    this.paginatedCourses = this.availableCourses.slice(0, 10);
                    this.totalPages = Math.ceil(this.availableCourses.length / 10);
                })
                .catch(error => {
                    console.error(error);
                });
        } else {
            this.getAvailableCourses();
        }


    },
    searchSelectedCourses: function(event) {
        if (event.target.value.length >= 3) {
            this.paginatedSelectedCourses = this.selectedCourses.filter(course => course.name.toLowerCase().includes(event.target.value.toLowerCase()));
        } else {
            this.paginatedSelectedCourses = this.selectedCourses;
        }
    },
    addCourse: function(id) {
        if (this.selectedCourses.find(course => course.id === id)) {
            return;
        }
        let course = this.availableCourses.find(course => course.id === id);
        this.selectedCourses.push(course);
        this.paginatedSelectedCourses = this.selectedCourses;
        this.updateFilterJson()
    },
    removeCourse: function(id) {
        this.selectedCourses = this.selectedCourses.filter(course => course.id !== id);
        this.paginatedSelectedCourses = this.selectedCourses;
        this.updateFilterJson()
    },
    goToPage: function(page) {
        if (page < 1 || page > this.totalPages) {
            return;
        }

        this.currentPage = page;
        this.paginatedCourses = this.availableCourses.slice((page - 1) * 10, page * 10);
    },
    updateFilterJson: function() {
        this.filtersJson = JSON.stringify(this.selectedCourses);
        this.validateForm()
    },
    validateForm: function() {
        if ((this.selectedCourses.length > 0) && (this.usersType !== '')) {
            this.isSubmitEnabled = true;
            return;
        }

        this.isSubmitEnabled = false;
    },
    init: function() {
        this.getAvailableCourses();

        $watch('usersType', value => {
            this.validateForm()
        })
    }
}">
    @csrf

    <input name="type" type="hidden" value="users_course">
    <input name="filters" type="hidden" x-model="filtersJson">

    <div class="w-1/2">

        <x-form.select name="users_type" label="Users type" required="{{ true }}" :options="[
            ['label' => 'Athletes only', 'value' => 'athletes'],
            ['label' => 'Personnel only', 'value' => 'personnel'],
            ['label' => 'All users', 'value' => 'all_users'],
        ]"
            x-model="usersType" shouldHaveEmptyOption="true" x-on:change="validateForm()" />

    </div>

    <p class="my-4">{{ __('exports.users_course_filter_message') }}</p>

    <div class="grid grid-cols-2 gap-2">
        <div class="bg-background-900 p-4 rounded">
            <div class="flex justify-between gap-2 items-center">
                <div class="flex-1">
                    <h4 class="text-background-800 dark:text-background-200 text-lg">
                        {{ __('exports.available_courses') }}
                    </h4>
                </div>
                <div>
                    <x-text-input type="text" x-on:input="searchAvailableCourses(event);" placeholder="Search..."
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
                            <td class="text-background-500 dark:text-background-300 text-sm" x-text="row.name"></td>
                            <td class="text-background-500 dark:text-background-300 text-sm" x-text="row.school"></td>
                            <td class="text-background-500 dark:text-background-300 text-sm text-right p-1">
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
                    <button type="button" x-on:click="goToPage(1)" class="mr-2" x-bind:disabled="currentPage === 1">
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

        <div class="bg-background-900 p-4 rounded">
            <div class="flex justify-between gap-2 items-center">
                <div class="flex-1">
                    <h4 class="text-background-800 dark:text-background-200 text-lg">
                        {{ __('exports.selected_courses') }}
                    </h4>

                </div>

                <div>
                    <x-text-input type="text" x-on:input="searchAvailableCourses(event);" placeholder="Search..."
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
                    <template x-for="(row, index) in paginatedSelectedCourses">
                        <tr>
                            <td class="text-background-500 dark:text-background-300 text-sm" x-text="row.name"></td>
                            <td class="text-background-500 dark:text-background-300 text-sm" x-text="row.school"></td>
                            <td class="text-background-500 dark:text-background-300 text-sm text-right p-1">
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

    <div class="flex justify-end w-full my-4">
        <button type="submit" :disabled="!isSubmitEnabled"
            class="inline-flex items-center px-4 py-2 bg-primary-800 dark:bg-primary-400 border border-transparent rounded-md font-semibold text-xs text-white dark:text-background-800 uppercase tracking-widest hover:bg-background-700 dark:hover:bg-primary-600 focus:bg-background-700 dark:focus:bg-primary-500 active:bg-background-900 dark:active:bg-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-background-800 transition ease-in-out duration-150 disabled:cursor-not-allowed disabled:pointer-events-none disabled:opacity-60 ">
            {{ __('exports.submit') }}
        </button>
    </div>


</form>
