@props([
    'roles' => [],
    'selected' => '[]',
])

<div class="w-full" x-data="{
    roles: {{ collect($roles) }},
    selected: [],
    selectedIds: JSON.parse('{{ $selected }}'),
    selectedIdsJson: '',
    addRole: function(role) {
        this.selected.push(role);
        this.selectedIds.push(role.id);
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    removeRole: function(role) {
        this.selected = this.selected.filter(selectedRole => selectedRole.id !== role.id);
        this.selectedIds = this.selectedIds.filter(selectedId => selectedId !== role.id);
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    isRoleSelected: function(roleId) {
        return this.selectedIds.includes(roleId);
    },
    addAllRoles: function() {
        this.selected = this.roles;
        this.selectedIds = this.roles.map(role => role.id);
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    removeAllRoles: function() {
        this.selected = [];
        this.selectedIds = [];
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    },
    init() {
        console.log(this.roles);
        this.selected = this.selectedIds.map(id => this.roles.find(role => role.id === id));
        this.selectedIdsJson = JSON.stringify(this.selectedIds);
    }
}">
    <div class="flex items-center w-full">
        <h3 class="text-lg font-medium text-background-900 dark:text-background-100 flex-1">
            {{ __('announcements.roles') }}
        </h3>
        <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', 'select-roles-modal')">
            {{ __('announcements.select_roles') }}
        </x-primary-button>
    </div>

    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

    <ul class="mt-1 list-disc">
        <template x-for="role in selected" :key="role.id">
            <li class="text-background-900 dark:text-background-100" x-text="role.name"></li>
        </template>
    </ul>

    <input type="hidden" name="selectedRoles" x-model="selectedIdsJson">

    <x-modal name="select-roles-modal" :show="$errors->customrole->isNotEmpty()" maxWidth="7xl" focusable>
        <div class="p-6 flex flex-col gap-2">
            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('announcements.select_roles') }}
            </h2>
            <div class="flex gap-2">
                <x-primary-button x-on:click.prevent="addAllRoles" >
                    <x-lucide-plus class="w-5 h-5 text-white" />
                </x-primary-button>
                <x-primary-button x-on:click.prevent="removeAllRoles" x-show="selectedIds.length > 0">
                    <x-lucide-minus class="w-5 h-5 text-white" />
                </x-primary-button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-table :columns="[
                    [
                        'name' => 'Name',
                        'attribute' => 'name',
                    ],
                    [
                        'name' => 'Actions',
                        'field' => 'actions',
                    ],
                ]" :rows="$roles">
                    <x-slot name="tableRows">
                        <td class="text-background-500 dark:text-background-300 px-6 py-1 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.name"></td>
                        <td
                            class="text-background-500 dark:text-background-300 px-6 py-1 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                            <x-primary-button x-on:click.prevent="addRole(row)" x-show="!isRoleSelected(row.id)">
                                <x-lucide-plus class="w-5 h-5 text-white" />
                            </x-primary-button>
                            <x-primary-button x-on:click.prevent="removeRole(row)" x-show="isRoleSelected(row.id)">
                                <x-lucide-minus class="w-5 h-5 text-white" />
                            </x-primary-button>
                        </td>
                    </x-slot>
                </x-table>

                <div>
                    <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                        {{ __('announcements.selected_roles') }}</h4>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="grid grid-cols-4 gap-2 text-background-900 dark:text-background-100">
                        <template x-for="role in selected" :key="role.id">
                            <div class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer hover:bg-primary-500 dark:hover:bg-primary-500 hover:text-white dark:hover:text-white"
                                x-on:click="removeRole(role)" x-text="role.name"></div>
                        </template>
                    </div>
                </div>

            </div>

            <div class="flex items-end justify-end gap-4">
                <x-primary-button x-on:click.prevent="$dispatch('close-modal', 'select-roles-modal')">
                    {{ __('announcements.close') }}
                </x-primary-button>
            </div>
        </div>
    </x-modal>
</div>
