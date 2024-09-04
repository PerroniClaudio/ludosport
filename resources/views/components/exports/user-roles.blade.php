@props(['roles' => []])
@php
    $authRole = auth()->user()->getRole();
    $actionRoute = $authRole === 'admin' ? 'exports.store' : $authRole . '.exports.store';
@endphp
<form action="{{ route($actionRoute) }}" method="POST" x-data="{
    selectedRoles: [],
    selectedRolesJson: '',
    isSubmitEnabled: false,
    valuateRoles: function() {
        if (this.selectedRoles.length > 0) {
            this.isSubmitEnabled = true;
            this.selectedRolesJson = JSON.stringify(this.selectedRoles.map(role => role.id));
            return;
        }

        this.isSubmitEnabled = false;
    },
    roles: {{ collect($roles) }},
    addRole: function(id) {
        if(this.selectedRoles.find(role => role.id === id)) {
            return;
        }
        let role = this.roles.find(role => role.id === id);
        this.selectedRoles.push(role);
        this.valuateRoles();
    },
    removeRole: function(id) {
        this.selectedRoles = this.selectedRoles.filter(role => role.id !== id);
        this.valuateRoles();
    },

}">

    @csrf

    <p class="my-4">{{ __('exports.user_roles_filter_message') }}</p>
    <input name="type" type="hidden" value="user_roles">
    <input name="selected_roles" type="hidden" x-model="selectedRolesJson">

    <div class="grid grid-cols-2 gap-2">
        <div class="bg-background-900 p-4 rounded">
            <h4 class="text-background-800 dark:text-background-200 text-lg">{{ __('exports.available_roles') }}</h4>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <table
                class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                <thead>
                    <tr>
                        <th
                            class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                            {{ __('exports.roles_name') }}</th>
                        <th
                            class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                            {{ __('exports.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in roles">
                        <tr>
                            <td class="text-background-500 dark:text-background-300 text-sm" x-text="row.name"></td>
                            <td class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                <button type="button" @click="addRole(row.id)">
                                    <x-lucide-plus
                                        class="w-4 h-4 text-primary-500 dark:text-primary-400 hover:text-primary-700" />
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="bg-background-900 p-4 rounded">
            <h4 class="text-background-800 dark:text-background-200 text-lg">{{ __('exports.selected_roles') }}</h4>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <table
                class="border-collapse table-auto w-full whitespace-no-wrap bg-white dark:bg-background-900 table-striped relative flex-1">
                <thead>
                    <tr>
                        <th
                            class="text-left bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                            {{ __('roles.name') }}</th>
                        <th
                            class="text-right bg-background-100 dark:bg-background-900 sticky top-0 border-b border-background-100 dark:border-background-700 py-2 text-primary-500 dark:text-primary-400 font-bold tracking-wider uppercase text-xs truncate">
                            {{ __('roles.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in selectedRoles">
                        <tr>
                            <td class="text-background-500 dark:text-background-300 text-sm" x-text="row.name"></td>
                            <td class="text-background-500 dark:text-background-300 text-sm text-right p-1">
                                <button type="button" @click="removeRole(row.id)">
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
