@props([
    'weapon_form_id' => 0,
    'personnel' => [],
])

<div x-data="{
    selectedUsersJson: '',
    selectedUsers: [],
    addUser(user) {
        this.selectedUsers.push(user.id);
        this.selectedUsersJson = JSON.stringify(this.selectedUsers);
    },
    removeUser(user) {
        this.selectedUsers = this.selectedUsers.filter(id => id !== user.id);
        this.selectedUsersJson = JSON.stringify(this.selectedUsers);
    },
    shouldShowUser(user) {
        return !this.selectedUsers.includes(user.id);
    },
}">
    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'handpick-modal')">
        <x-lucide-plus class="w-6 h-6 text-white" />
    </x-primary-button>

    <x-modal name="handpick-modal" :show="$errors->userId->isNotEmpty()" focusable>
        <div class="p-6">

            <x-table striped="false" isDialogTable="true" :columns="[
                [
                    'name' => 'ID',
                    'field' => 'id',
                    'columnClasses' => '', // classes to style table th
                    'rowClasses' => '', // classes to style table td
                ],
                [
                    'name' => 'Name',
                    'field' => 'name',
                    'columnClasses' => '', // classes to style table th
                    'rowClasses' => '', // classes to style table td
                ],
                [
                    'name' => 'Role',
                    'field' => 'role',
                    'columnClasses' => '', // classes to style table th
                    'rowClasses' => '', // classes to style table td
                ],
            ]" :rows="$personnel">
                <x-slot name="tableActions">
                    <button type="button" x-on:click="addUser(row)" class="cursor-pointer" x-show="shouldShowUser(row)">
                        <x-lucide-plus class="w-5 h-5 text-primary-800 dark:text-primary-500" />
                    </button>
                    <button type="button" x-on:click="removeUser(row)" class="cursor-pointer"
                        x-show="!shouldShowUser(row)">
                        <x-lucide-minus class="w-5 h-5 text-primary-800 dark:text-primary-500" />
                    </button>
                </x-slot>
            </x-table>

            <form class="flex justify-end" method="POST"
                action="{{ route('weapon-forms.personnel.store', $weapon_form_id) }}">
                @csrf
                <input type="hidden" name="users" x-model="selectedUsersJson" />


                <x-primary-button>
                    {{ __('weaponf.save') }}
                </x-primary-button>

            </form>
        </div>
    </x-modal>
</div>
