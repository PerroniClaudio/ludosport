@props([
    'nations' => [],
    'selected' => '[]',
])

<div class="w-full" x-data="{
    nations: {{ collect($nations) }},
    selected: [],
    selectedIds: JSON.parse('{{ $selected }}'),
    selectedIdsJson: '',
    addNation: function(nation) {
        this.selected.push(nation);
        this.selectedIds.push(nation.id);
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    removeNation: function(nation) {
        this.selected = this.selected.filter(selectedNation => selectedNation.id !== nation.id);
        this.selectedIds = this.selectedIds.filter(selectedId => selectedId !== nation.id);
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    isNationSelected: function(nationId) {
        return this.selectedIds.includes(nationId);
    },
    addAllNations: function() {
        this.selected = this.nations;
        this.selectedIds = this.nations.map(nation => nation.id);
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    removeAllNations: function() {
        this.selected = [];
        this.selectedIds = [];
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    init() {
        this.selected = this.selectedIds.map(id => this.nations.find(nation => nation.id === id));
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    }
}">

    <div class="flex items-center w-full">
        <h3 class="text-lg font-medium text-background-900 dark:text-background-100 flex-1">
            {{ __('announcements.nations') }}
        </h3>
        <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', 'select-nation-modal')">
            {{ __('announcements.select_nations') }}
        </x-primary-button>
    </div>

    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>



    <ul class="mt-1 list-disc">
        <template x-for="nation in selected" :key="nation.id">
            <li class="text-background-900 dark:text-background-100" x-text="nation.name"></li>
        </template>
    </ul>

    <input type="hidden" name="selectedNations" x-model="selectedIdsJson">

    <x-modal name="select-nation-modal" :show="$errors->customrole->isNotEmpty()" maxWidth="7xl" focusable>
        <div class="p-6 flex flex-col gap-2">
            <div class="flex gap-2 items-center">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('announcements.select_nations') }}
                </h2>
                <div class='has-tooltip'>
                    <span class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 mr-12 translate-x-8 z-50'>
                        {{ __('announcements.select_nations_info') }}
                    </span>
                    <x-lucide-info class="h-4 text-background-400" />
                </div>
            </div>
            <div class="flex gap-2">
                <x-primary-button x-on:click.prevent="addAllNations" >
                    <x-lucide-plus class="w-5 h-5 text-white" />
                </x-primary-button>
                <x-primary-button x-on:click.prevent="removeAllNations" x-show="selectedIds.length > 0">
                    <x-lucide-minus class="w-5 h-5 text-white" />
                </x-primary-button>
            </div>

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
                ]" :rows="$nations">
                    <x-slot name="tableRows">
                        <td class="text-background-500 dark:text-background-300 px-6 py-1 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.name"></td>
                        <td
                            class="text-background-500 dark:text-background-300 px-6 py-1 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                            <x-primary-button x-on:click.prevent="addNation(row)" x-show="!isNationSelected(row.id)">
                                <x-lucide-plus class="w-5 h-5 text-white" />
                            </x-primary-button>
                            <x-primary-button x-on:click.prevent="removeNation(row)" x-show="isNationSelected(row.id)">
                                <x-lucide-minus class="w-5 h-5 text-white" />
                            </x-primary-button>
                        </td>
                    </x-slot>
                </x-table>
                <div>
                    <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                        {{ __('announcements.selected_nations') }}</h4>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="grid grid-cols-4 gap-2 text-background-900 dark:text-background-100">
                        <template x-for="nation in selected" :key="nation.id">
                            <div class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer hover:bg-primary-500 dark:hover:bg-primary-500 hover:text-white dark:hover:text-white"
                                x-on:click="removeNation(nation)" x-text="nation.name"></div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex items-end justify-end gap-4">
                <x-primary-button x-on:click.prevent="$dispatch('close-modal', 'select-nation-modal')">
                    {{ __('announcements.close') }}
                </x-primary-button>
            </div>
        </div>


    </x-modal>

</div>
