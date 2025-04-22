<div x-data="{
    isDialogOpen: true,
    selectedAcademyId: null,
    selectedSchoolId: null,
    academies: [],
    schools: [],
    fetchAcademies: function() {
        let search = this.academyNeedle;
        if (search && search.length >= 2) {
            fetch('/registration/academy-search?search=' + search)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        this.academies = data;
                    }
                });
        } else {
            this.academies = [];
            this.selectedAcademyId = null;
        }
    },
    fetchSchools: function() {
        fetch('/registration/school-search?academy_id=' + this.selectedAcademyId)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    this.schools = data;
                }
            });
    },
    selectedAcademy: {{ $selectedvalue ? "'" . $selectedvalue . "'" : "'Select an academy and a school'" }},
    academyNeedle: null,
    toggleBodyScroll: function(isOpen) {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    }
}" x-init="() => {
    toggleBodyScroll(isDialogOpen);
    $watch('academyNeedle', value => {
        if (value && value.length >= 2) {
            fetchAcademies();
        }
    });
    $watch('selectedAcademyId', value => {
        if (value) {
            fetchSchools();
        }
    });
    $watch('isDialogOpen', value => {
        toggleBodyScroll(value);
    });
}">
    <x-input-label for="academy" value="{{ __('users.academy_and_school') }}" />
    <div class="flex w-full gap-2 mt-1">
        <input type="hidden" name="academy_id" x-model="selectedAcademyId">
        <input type="hidden" name="school_id" x-model="selectedSchoolId">
        <x-text-input disabled name="academy" class="flex-1" type="text" x-model="selectedAcademy" />
        <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
            x-on:click="isDialogOpen = true">
            <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
        </div>
    </div>
    <x-input-error :messages="$errors->get('academy')" class="mt-2" />

    <div class="modal" role="dialog" tabindex="-1" x-show="isDialogOpen" x-on:click.away="isDialogOpen = false"
        x-cloak x-transition>

        <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50 ">
            <div class="flex items-center justify-center min-h-screen">
                <div
                    class="bg-background-100 dark:bg-background-800 dark:text-background-300 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">
                            {{ __('users.select_academy_school') }}</h2>
                        <div class="cursor-pointer" x-on:click="isDialogOpen = false">
                            <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                        </div>
                    </div>

                    <div class="mt-4">

                        <div>
                            <x-form.input-model label="{{ __('users.sas_academy_search_label') }}" name="academyNeedle"
                                placeholder="{{ __('users.sas_academy_search_placeholder') }}" />
                        </div>

                        <div class="p-4 flex items-center justify-center"
                            x-show="academyNeedle != null && academyNeedle.length > 2 && academies.length == 0">
                            <p>{{ __('users.sas_academy_search_no_results') }}</p>
                        </div>

                        <div class="grid lg:grid-cols-2 gap-4 py-4"
                            x-show="academyNeedle != null && academyNeedle.length > 2 && academies.length > 0">

                            <template x-for="academy in academies" :key="academy.id">

                                <div class="p-4 border rounded-lg cursor-pointer "
                                    :class="selectedAcademyId === academy.id ?
                                        'bg-primary-200 dark:bg-primary-600 text-background-600 dark:text-background-100 border-2 border-primary-500 dark:border-primary-400' :
                                        'hover:bg-primary-100 dark:hover:bg-primary-600 hover:text-background-100 text-background-600 dark:text-background-400 border-background-100 dark:border-background-700'"
                                    x-on:click="selectedAcademyId = academy.id; selectedAcademy = academy.name;">
                                    <h3 class="font-semibold" x-text="academy.name"></h3>
                                    <p class="text-sm" x-text="academy.nation"></p>
                                </div>

                            </template>

                        </div>

                        <section x-show="selectedAcademyId != null" class="mt-4">

                            <div>
                                <h2 class="font-semibold text-background-500 dark:text-background-300">
                                    {{ __('users.sas_available_schools') }}</h2>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            </div>

                            <div class="p-4 flex items-center justify-center" x-show="schools.length == 0">
                                <p>{{ __('users.sas_academy_search_no_results') }}</p>
                            </div>

                            <div class="mt-4" x-show="schools.length > 0">
                                <x-input-label for="school" value="{{ __('users.sas_select_school_label') }}" />
                                <select id="school" name="school_id" x-model="selectedSchoolId"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-background-700 dark:border-background-600 dark:text-background-300">
                                    <option value="">{{ __('users.sas_select_school_placeholder') }}</option>
                                    <template x-for="school in schools" :key="school.id">
                                        <option :value="school.id" x-text="school.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="flex flex-row-reverse mt-4">
                                <x-primary-button type="button" x-bind:disabled="!selectedSchoolId"
                                    x-on:click="isDialogOpen = false">
                                    {{ __('users.sas_save_selection') }}
                                </x-primary-button>
                            </div>

                        </section>

                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
