@props([
    'academy' => null,
])
<div x-data="{
    selectedAcademyId: '{{ $academy->id }}',
    selectedRectorId: '{{ $academy->mainRector ? $academy->mainRector->id : 0 }}',
    selectedRector: '{{ $academy->mainRector ? $academy->mainRector->name . ' ' . ($academy->mainRector->surname ?? '') : '' }}',
    isRectorDialogOpen: false,
    availableRectors: [],
    allRectors: [],
    showAvailableRectorModal() {
        const url = `/academies/${this.selectedAcademyId}/available-rectors`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                this.allRectors = data.rectors.map(rector => ({
                    id: rector.id,
                    name: `${rector.name} ${rector.surname || ''}`,
                    email: rector.email,
                }));
                this.availableRectors = [...this.allRectors];

                this.isRectorDialogOpen = true;
            })
            .catch(error => console.error('Error fetching available rectors:', error));
    },
    searchRectorByValue(event) {
        const searchValue = event.target.value.toLowerCase();
        this.availableRectors = this.allRectors.filter(rector => rector.name.toLowerCase().includes(searchValue));
    }
}">
    <div class="w-full flex flex-col gap-2">
        <div class="flex gap-2">
            <x-input-label for="rector" value="{{ __('users.rector') }}" />
            <div class="has-tooltip">
                <x-lucide-info class="h-4 text-background-300" />
                <div
                    class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                    {{ __('academies.academy_rector_description') }}
                </div>
            </div>

        </div>
        <div class="flex w-full gap-2">
            <input type="hidden" name="main_rector" x-model="selectedRectorId" />
            <x-text-input :disabled="true" placeholder="Select a rector" name="rector" class="flex-1" type="text"
                x-model="selectedRector" />

            @if (auth()->user()->getRole() === 'admin')
                <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
                    x-on:click="showAvailableRectorModal()">
                    <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
                </div>
            @endif
        </div>
    </div>

    @if (auth()->user()->getRole() === 'admin')
        <div class="modal" role="dialog" tabindex="-1" x-show="isRectorDialogOpen"
            x-on:click.away="isRectorDialogOpen = false" x-cloak x-transition>
            <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50">
                <div class="flex items-center justify-center min-h-screen">
                    <div class="bg-background-100 dark:bg-background-800 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">
                                {{ __('academies.select_rector') }}</h2>
                            <div class="cursor-pointer" x-on:click="isRectorDialogOpen = false">
                                <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                            </div>
                        </div>
                        <div
                            class="mb-5 overflow-x-auto bg-white dark:bg-background-900 rounded-lg shadow overflow-y-auto relative min-h-[600px] flex flex-col justify-between max-h-[80vh]">

                            <div class="flex justify-between items-center p-6">
                                <div class="flex items-center justify-end w-full">
                                    <x-text-input type="text" x-on:input="searchRectorByValue($event)"
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
                                    <template x-if="availableRectors.length === 0">
                                        <tr>
                                            <td colspan="100%"
                                                class="text-center text-background-500 dark:text-background-300 py-10 px-4 text-sm">
                                                No records found
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="(rector, index) in availableRectors" :key="'rector-' + index">
                                        <tr class="hover:bg-background-200 dark:hover:bg-background-900 cursor-pointer"
                                            x-on:click="selectedRectorId = rector.id; selectedRector = rector.name; isRectorDialogOpen = false;">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                x-text="rector.id" />
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300"
                                                x-text="rector.name" />
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-background-500 dark:text-background-300">
                                                <x-primary-button type="button"
                                                    x-on:click="selectedRector = rector.name; selectedRectorId = rector.id; isRectorDialogOpen = false;">{{ __('users.select') }}</x-primary-button>
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
