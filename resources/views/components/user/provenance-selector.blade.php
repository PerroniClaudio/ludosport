{{-- 
    🫡 non più usato
--}}

@props([
    'nationality' => '',
    'selectedAcademyId' => '',
    'selectedAcademy' => '',
    'selectedSchool' => '',
    'selectedSchoolId' => '',
    'nations' => [],
    'academies' => [],
    'schools' => [],
])

<div x-data="{
    selectedNationality: '{{ $nationality }}',
    selectedAcademyId: '{{ $selectedAcademyId }}',
    selectedAcademy: '{{ $selectedAcademy ? $selectedAcademy : 'Select an academy' }}',
    selectedSchool: '{{ $selectedSchool ? $selectedSchool : 'Select a school' }}',
    selectedSchoolId: '{{ $selectedSchoolId }}',
    academies: {{ collect($academies) }},
    schools: {{ collect($schools) }},
    isAcademyDialogOpen: false,
    isSchoolDialogOpen: false,
    searchSchoolByValue(e) {
        const search = e.target.value.toLowerCase();
        if (search === '') {
            this.getSchools();
        } else {
            this.schools = this.schools.filter(school => {
                return school.name.toLowerCase().includes(search);
            });
        }
    },
    searchAcademyByValue(e) {
        const search = e.target.value.toLowerCase();
        if (search === '') {
            this.getAcademies();
        } else {
            this.academies = this.academies.filter(academy => {
                return academy.name.toLowerCase().includes(search);
            });
        }
    },
    updateNationId() {
        this.selectedAcademyId = '';
        this.selectedSchoolId = '';
        this.getAcademies();

    },
    updateAcademyId() {
        this.selectedSchoolId = '';
        this.getSchools();
    },
    getAcademies() {
        const url = `/nation/${this.selectedNationality}/academies`;
        fetch(url, {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(data => data.json())
            .then(res => this.academies = res)
            .catch(e => console.log(e))
    },
    getSchools() {
        const url = `/academy/${this.selectedAcademyId}/schools`;
        fetch(url, {
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(data => data.json())
            .then(res => this.schools = res)
            .catch(e => console.log(e))
    }
}" class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8"">

    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.provenance') }}</h3>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <div class="w-1/2 flex flex-col gap-2">
        <div>
            <x-input-label for="nationality" value="Nationality" />
            <select x-model="selectedNationality" x-on:change="updateNationId()" name="nationality" id="nationality"
                class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
                @foreach ($nations as $key => $nation)
                    <optgroup label="{{ $key }}"">
                        @foreach ($nation as $n)
                            <option value="{{ $n['id'] }}" {{ $n['id'] == $nationality ? 'selected' : '' }}>
                                {{ $n['name'] }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div id="academy-container">
            <x-input-label for="academy" value="{{ __('users.academy') }}" />
            <div class="flex w-full gap-2">
                <input type="hidden" name="academy_id" x-model="selectedAcademyId">
                <x-text-input disabled name="academy" class="flex-1" type="text" x-model="selectedAcademy" />
                <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
                    x-on:click="isAcademyDialogOpen = true">
                    <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
                </div>
            </div>
            <x-input-error :messages="$errors->get('academy_id')" class="mt-2" />

            <div class="modal" role="dialog" tabindex="-1" x-show="isAcademyDialogOpen"
                x-on:click.away="isAcademyDialogOpen = false" x-cloak x-transition>
                <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50">
                    <div class="flex items-center justify-center min-h-screen">
                        <div class="bg-background-100 dark:bg-background-800 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                            <div class="flex justify-between items-center">
                                <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">
                                    {{ __('users.select_academy') }}</h2>
                                <div class="cursor-pointer" x-on:click="isAcademyDialogOpen = false">
                                    <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                                </div>
                            </div>
                            <div class="mt-4">
                                <div
                                    class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative min-h-[600px] flex flex-col justify-between max-h-[80vh]">

                                    <div class="flex justify-between items-center p-6">
                                        <div class="flex items-center justify-end w-full">
                                            <x-text-input type="text" x-on:input="searchAcademyByValue($event)"
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
                                                    Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <template x-if="academies.length === 0">
                                                <tr>
                                                    <td colspan="100%"
                                                        class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                                        No records found
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-for="(academy, index) in academies" :key="'academy-' + index">
                                                <tr class="hover:bg-background-200 dark:hover:bg-background-900 cursor-pointer"
                                                    x-on:click="selectedAcademyId = academy.id; selectedAcademy = academy.name; isAcademyDialogOpen = false; updateAcademyId();">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                        x-text="academy.id" />
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                        x-text="academy.name" />
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300">
                                                        <x-primary-button type="button"
                                                            x-on:click="selectedAcademy = academy.name; selectedAcademyId = academy.id; isAcademyDialogOpen = false; updateAcademyId();">{{ __('users.select') }}</x-primary-button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="school-container">
            <x-input-label for="school" value="{{ __('users.school') }}" />
            <div class="flex w-full gap-2">
                <input type="hidden" name="school_id" x-model="selectedSchoolId">
                <x-text-input disabled name="school" class="flex-1" type="text" x-model="selectedSchool" />
                <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
                    x-on:click="isSchoolDialogOpen = true">
                    <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
                </div>
            </div>
            <x-input-error :messages="$errors->get('school_id')" class="mt-2" />

            <div class="modal" role="dialog" tabindex="-1" x-show="isSchoolDialogOpen"
                x-on:click.away="isSchoolDialogOpen = false" x-cloak x-transition>
                <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50">
                    <div class="flex items-center justify-center min-h-screen">
                        <div class="bg-background-100 dark:bg-background-800 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                            <div class="flex justify-between items-center">
                                <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">
                                    {{ __('users.select_school') }}</h2>
                                <div class="cursor-pointer" x-on:click="isSchoolDialogOpen = false">
                                    <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                                </div>
                            </div>
                            <div class="mt-4">
                                <div
                                    class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative min-h-[600px] flex flex-col justify-between max-h-[80vh]">

                                    <div class="flex justify-between items-center p-6">
                                        <div class="flex items-center justify-end w-full">
                                            <x-text-input type="text" x-on:input="searchSchoolByValue($event)"
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
                                                    Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <template x-if="academies.length === 0">
                                                <tr>
                                                    <td colspan="100%"
                                                        class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                                        No records found
                                                    </td>
                                                </tr>
                                            </template>
                                            <template x-for="(school, index) in schools" :key="'school-' + index">
                                                <tr class="hover:bg-background-200 dark:hover:bg-background-900 cursor-pointer"
                                                    x-on:click="selectedSchool = school.name; selectedSchoolId = school.id; isSchoolDialogOpen = false;">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                        x-text="school.id" />
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                        x-text="school.name" />
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300">
                                                        <x-primary-button type="button"
                                                            x-on:click="selectedSchool = school.name; selectedSchoolId = school.id; isSchoolDialogOpen = false;">{{ __('users.select') }}</x-primary-button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
