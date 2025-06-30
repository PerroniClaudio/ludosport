@props([
    'school' => null,
])
<div x-data="{
    selectedSchoolId: '{{ $school->id }}',
    selectedDeanId: '{{ $school->mainDean ? $school->mainDean->id : 0 }}',
    selectedDean: '{{ $school->mainDean ? $school->mainDean->name . ' ' . ($school->mainDean->surname ?? '') : '' }}',
    isDeanDialogOpen: false,
    availableDeans: [],
    showAvailableDeanModal() {
        const url = `/schools/${this.selectedSchoolId}/available-deans`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                this.availableDeans = data.deans.map(dean => ({
                    id: dean.id,
                    name: `${dean.name} ${dean.surname || ''}`,
                    email: dean.email,
                }));

                this.isDeanDialogOpen = true;
            })
            .catch(error => console.error('Error fetching available deans:', error));
    },
    searchDeanByValue(event) {
        const searchValue = event.target.value.toLowerCase();
        this.academies = this.availableDeans.filter(dean => dean.name.toLowerCase().includes(searchValue));
    }
}">
    <div class="w-full flex flex-col gap-2">
        <div class="flex gap-2">
            <x-input-label for="academy" value="{{ __('users.dean') }}" />
            <div class="has-tooltip">
                <x-lucide-info class="h-4 text-background-300" />
                <div
                    class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                    {{ __('school.school_dean_description') }}
                </div>
            </div>

        </div>
        <div class="flex w-full gap-2">
            <input type="hidden" name="main_dean" x-model="selectedDeanId" />
            <x-text-input :disabled="true" placeholder="Select a dean" name="dean" class="flex-1" type="text"
                x-model="selectedDean" />

            @if (auth()->user()->getRole() === 'admin')
                <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
                    x-on:click="showAvailableDeanModal()">
                    <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
                </div>
            @endif
        </div>
    </div>

    @if (auth()->user()->getRole() === 'admin')
        <div class="modal" role="dialog" tabindex="-1" x-show="isDeanDialogOpen"
            x-on:click.away="isDeanDialogOpen = false" x-cloak x-transition>
            <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50">
                <div class="flex items-center justify-center min-h-screen">
                    <div class="bg-background-100 dark:bg-background-800 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">
                                {{ __('school.select_dean') }}</h2>
                            <div class="cursor-pointer" x-on:click="isDeanDialogOpen = false">
                                <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                            </div>
                        </div>
                        <div
                            class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative min-h-[600px] flex flex-col justify-between max-h-[80vh]">

                            <div class="flex justify-between items-center p-6">
                                <div class="flex items-center justify-end w-full">
                                    <x-text-input type="text" x-on:input="searchDeanByValue($event)"
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
                                    <template x-if="availableDeans.length === 0">
                                        <tr>
                                            <td colspan="100%"
                                                class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                                No records found
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="(dean, index) in availableDeans" :key="'dean-' + index">
                                        <tr class="hover:bg-background-200 dark:hover:bg-background-900 cursor-pointer"
                                            x-on:click="selectedDeanId = dean.id; selectedDean = dean.name; isDeanDialogOpen = false;">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                x-text="dean.id" />
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                x-text="dean.name" />
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300">
                                                <x-primary-button type="button"
                                                    x-on:click="selectedDean = dean.name; selectedAcademyId = dean.id; isDeanDialogOpen = false;">{{ __('users.select') }}</x-primary-button>
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
    @endif

</div>
