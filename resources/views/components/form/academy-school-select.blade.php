<div x-data="{
    isAcademyDialogOpen: false,
    isSchoolDialogOpen: false,
    pendingAcademyId: null,
    pendingAcademy: '',
    pendingSchoolId: null,
    pendingSchool: '',
    selectedAcademyId: null,
    selectedAcademy: {{ $selectedvalue ? "'" . $selectedvalue . "'" : "''" }},
    selectedSchoolId: null,
    selectedSchool: '',
    academies: [],
    schools: [],
    academyNeedle: null,
    fetchAcademies() {
        const search = this.academyNeedle;
        if (search && search.length >= 2) {
            fetch('/registration/academy-search?search=' + search)
                .then(response => response.json())
                .then(data => {
                    this.academies = data.length > 0 ? data : [];
                });
            return;
        }

        this.academies = [];
        this.pendingAcademyId = null;
        this.pendingAcademy = '';
    },
    fetchSchools() {
        if (!this.selectedAcademyId) {
            this.schools = [];
            return;
        }

        fetch('/registration/school-search?academy_id=' + this.selectedAcademyId)
            .then(response => response.json())
            .then(data => {
                this.schools = data.length > 0 ? data : [];
            });
    },
    confirmAcademySelection() {
        if (!this.pendingAcademyId) {
            return;
        }

        const academyChanged = String(this.selectedAcademyId) !== String(this.pendingAcademyId);
        this.selectedAcademyId = this.pendingAcademyId;
        this.selectedAcademy = this.pendingAcademy;

        if (academyChanged) {
            this.selectedSchoolId = null;
            this.selectedSchool = '';
            this.pendingSchoolId = null;
            this.pendingSchool = '';
            this.schools = [];
            this.fetchSchools();
        }

        this.isAcademyDialogOpen = false;
    },
    openSchoolDialog() {
        if (!this.selectedAcademyId) {
            return;
        }

        this.pendingSchoolId = this.selectedSchoolId;
        this.pendingSchool = this.selectedSchool;
        this.fetchSchools();
        this.isSchoolDialogOpen = true;
    },
    confirmSchoolSelection() {
        if (!this.pendingSchoolId) {
            return;
        }

        this.selectedSchoolId = this.pendingSchoolId;
        this.selectedSchool = this.pendingSchool;
        this.isSchoolDialogOpen = false;
    },
    syncBodyScroll() {
        document.body.style.overflow = (this.isAcademyDialogOpen || this.isSchoolDialogOpen) ? 'hidden' : 'auto';
    }
}" x-init="() => {
    $watch('academyNeedle', value => {
        if (value && value.length >= 2) {
            fetchAcademies();
            return;
        }

        academies = [];
        pendingAcademyId = selectedAcademyId;
        pendingAcademy = selectedAcademy;
    });
    $watch('isAcademyDialogOpen', () => syncBodyScroll());
    $watch('isSchoolDialogOpen', () => syncBodyScroll());
}">
    <x-input-label for="academy" value="{{ __('users.academy') }}" />
    <div class="flex w-full gap-2 mt-1">
        <input type="hidden" name="academy_id" x-model="selectedAcademyId">
        <input type="hidden" name="school_id" x-model="selectedSchoolId">
        <x-text-input disabled name="academy" class="flex-1" type="text" x-model="selectedAcademy"
            placeholder="{{ __('users.select_academy') }}" />
        <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
            x-on:click="pendingAcademyId = selectedAcademyId; pendingAcademy = selectedAcademy; isAcademyDialogOpen = true">
            <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
        </div>
    </div>
    <x-input-error :messages="$errors->get('academy')" class="mt-2" />

    <div class="mt-4">
        <x-input-label for="school_preview" value="{{ __('users.school') }}" />
        <div class="flex w-full gap-2 mt-1">
            <x-text-input id="school_preview" disabled class="flex-1" type="text" x-model="selectedSchool"
                x-bind:placeholder="selectedAcademyId ? '{{ __('users.sas_select_school_placeholder') }}' : '{{ __('users.sas_select_academy_first') }}'" />
            <div class="p-2 rounded-full"
                :class="selectedAcademyId
                    ? 'text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 cursor-pointer'
                    : 'text-background-300 dark:text-background-600 cursor-not-allowed'"
                x-on:click="openSchoolDialog()">
                <x-lucide-search class="w-6 h-6" />
            </div>
        </div>
        <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
    </div>

    <div class="modal" role="dialog" tabindex="-1" x-show="isAcademyDialogOpen" x-cloak x-transition>
        <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50">
            <div class="flex items-center justify-center min-h-screen">
                <div x-on:click.away="isAcademyDialogOpen = false"
                    class="bg-background-100 dark:bg-background-800 dark:text-background-300 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">
                            {{ __('users.select_academy') }}
                        </h2>
                        <div class="cursor-pointer" x-on:click="isAcademyDialogOpen = false">
                            <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-form.input-model label="{{ __('users.sas_academy_search_label') }}" name="academyNeedle"
                            placeholder="{{ __('users.sas_academy_search_placeholder') }}" />

                        <div class="p-4 flex items-center justify-center"
                            x-show="academyNeedle != null && academyNeedle.length > 2 && academies.length == 0">
                            <p>{{ __('users.sas_academy_search_no_results') }}</p>
                        </div>

                        <div class="grid lg:grid-cols-2 gap-4 py-4"
                            x-show="academyNeedle != null && academyNeedle.length > 2 && academies.length > 0">
                            <template x-for="academy in academies" :key="academy.id">
                                <div class="p-4 border rounded-lg cursor-pointer"
                                    :class="pendingAcademyId === academy.id
                                        ? 'bg-primary-200 dark:bg-primary-600 text-background-600 dark:text-background-100 border-2 border-primary-500 dark:border-primary-400'
                                        : 'hover:bg-primary-500 dark:hover:bg-primary-600 hover:text-background-100 text-background-600 dark:text-background-400 border-background-100 dark:border-background-700'"
                                    x-on:click="pendingAcademyId = academy.id; pendingAcademy = academy.name">
                                    <h3 class="font-semibold" x-text="academy.name"></h3>
                                    <p class="text-sm" x-text="academy.nation"></p>
                                </div>
                            </template>
                        </div>

                        <div class="flex flex-row-reverse mt-4" x-show="pendingAcademyId != null">
                            <x-primary-button type="button" x-on:click="confirmAcademySelection()">
                                {{ __('users.sas_save_selection') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" role="dialog" tabindex="-1" x-show="isSchoolDialogOpen" x-cloak x-transition>
        <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50">
            <div class="flex items-center justify-center min-h-screen">
                <div x-on:click.away="isSchoolDialogOpen = false"
                    class="bg-background-100 dark:bg-background-800 dark:text-background-300 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">
                            {{ __('users.select_school') }}
                        </h2>
                        <div class="cursor-pointer" x-on:click="isSchoolDialogOpen = false">
                            <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <div>
                            <h2 class="font-semibold text-background-500 dark:text-background-300">
                                {{ __('users.sas_available_schools') }}
                            </h2>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        </div>

                        <div class="p-4 flex items-center justify-center" x-show="schools.length == 0">
                            <p>{{ __('users.sas_school_search_no_results') }}</p>
                        </div>

                        <div class="grid lg:grid-cols-2 gap-4 py-4" x-show="schools.length > 0">
                            <template x-for="school in schools" :key="school.id">
                                <div class="p-4 border rounded-lg cursor-pointer"
                                    :class="pendingSchoolId === school.id
                                        ? 'bg-primary-200 dark:bg-primary-600 text-background-600 dark:text-background-100 border-2 border-primary-500 dark:border-primary-400'
                                        : 'hover:bg-primary-500 dark:hover:bg-primary-600 hover:text-background-100 text-background-600 dark:text-background-400 border-background-100 dark:border-background-700'"
                                    x-on:click="pendingSchoolId = school.id; pendingSchool = school.name">
                                    <h3 class="font-semibold" x-text="school.name"></h3>
                                </div>
                            </template>
                        </div>

                        <div class="flex flex-row-reverse mt-4" x-show="pendingSchoolId != null">
                            <x-primary-button type="button" x-on:click="confirmSchoolSelection()">
                                {{ __('users.sas_save_selection') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
