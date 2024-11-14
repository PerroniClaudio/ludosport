@props([
    'academies' => [],
    'selected' => '[]',
])

<div class="w-full" x-data="{
    academies: {{ collect($academies) }},
    selected: [],
    selectedIds: JSON.parse('{{ $selected }}'),
    selectedIdsJson: '',
    addAcademy: function(academy) {
        this.selected.push(academy);
        this.selectedIds.push(academy.id);
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    removeAcademy: function(academy) {
        this.selected = this.selected.filter(selectedAcademy => selectedAcademy.id !== academy.id);
        this.selectedIds = this.selectedIds.filter(selectedId => selectedId !== academy.id);
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    isAcademySelected: function(academyId) {
        return this.selectedIds.includes(academyId);
    },
    init() {
        console.log(this.selectedIds);
        this.selected = this.selectedIds.map(id => this.academies.find(academy => academy.id === id));
    }
}">

    <div class="flex items-center w-full">
        <h3 class="text-lg font-medium text-background-900 dark:text-background-100 flex-1">
            {{ __('announcements.academies') }}
        </h3>
        <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', 'select-academy-modal')">
            {{ __('announcements.select_academies') }}
        </x-primary-button>
    </div>

    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>



    <ul class="mt-1 list-disc">
        <template x-for="academy in selected" :key="academy.id">
            <li class="text-background-900 dark:text-background-100" x-text="academy.name"></li>
        </template>
    </ul>

    <input type="hidden" name="selectedAcademies" x-model="selectedIdsJson">

    <x-modal name="select-academy-modal" :show="$errors->customrole->isNotEmpty()" maxWidth="7xl" focusable>
        <div class="p-6 flex flex-col gap-2">
            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('announcements.select_academies') }}
            </h2>

            <div class="grid grid-cols-2 gap-4">
                <x-table :columns="[
                    [
                        'name' => 'Name',
                        'field' => 'name',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'Actions',
                        'field' => 'actions',
                    ],
                ]" :rows="$academies">
                    <x-slot name="tableRows">
                        <td class="text-background-500 dark:text-background-300 px-6 py-1 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.name"></td>
                        <td
                            class="text-background-500 dark:text-background-300 px-6 py-1 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                            <x-primary-button x-on:click.prevent="addAcademy(row)" x-show="!isAcademySelected(row.id)">
                                <x-lucide-plus class="w-5 h-5 text-white" />
                            </x-primary-button>
                            <x-primary-button x-on:click.prevent="removeAcademy(row)"
                                x-show="isAcademySelected(row.id)">
                                <x-lucide-minus class="w-5 h-5 text-white" />
                            </x-primary-button>
                        </td>
                    </x-slot>
                </x-table>
                <div>
                    <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                        {{ __('announcements.selected_academies') }}</h4>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="grid grid-cols-4 gap-2 text-background-900 dark:text-background-100">
                        <template x-for="academy in selected" :key="academy.id">
                            <div class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer hover:bg-primary-500 dark:hover:bg-primary-500 hover:text-white dark:hover:text-white"
                                x-on:click="removeAcademy(academy)" x-text="academy.name"></div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-end justify-end gap-4">
                <x-primary-button x-on:click.prevent="$dispatch('close-modal', 'select-academy-modal')">
                    {{ __('announcements.close') }}
                </x-primary-button>
            </div>
        </div>


    </x-modal>

</div>
