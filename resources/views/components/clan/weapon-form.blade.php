@props([
    'selected_weapon' => [],
    'available_weapons' => [],
    'disabled' => false,
])

<div x-data="{
    weapon: {{ collect($selected_weapon) }},
    weaponFormId: 0,
    weaponFormName: 'Select Weapon Form',
    openModal() {
        this.$dispatch('open-modal', 'weapon-form-modal');
    },
    init() {

        if (this.weapon.id != undefined) {
            this.weaponFormId = this.weapon.id;
            this.weaponFormName = this.weapon.name;
        }

        if (this.weapon.length == 0) {
            this.weaponFormId = 0;
            this.weaponFormName = 'Other';
        }
    },
}">
    <x-input-label for="weapon_form_id" value="{{ __('events.weapon_form') }}" />
    <div class="flex w-full gap-2">
        <input type="hidden" name="weapon_form_id" x-model="weaponFormId">
        <x-text-input disabled name="Weapon Form" class="flex-1" type="text" x-model="weaponFormName" />
        @if (!$disabled)
            <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer"
                x-on:click="openModal()">
                <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
            </div>
        @endif
    </div>
    <x-input-error :messages="$errors->get('weapon_form')" class="mt-2" />

    @if (!$disabled)
        <x-modal name="weapon-form-modal" :show="$errors->userId->isNotEmpty()" focusable>
            <div class="p-6">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('events.weapon_form') }}
                </h2>
                <x-table striped="false" :columns="[
                    [
                        'name' => 'Id',
                        'field' => 'id',
                        'columnClasses' => '',
                        'rowClasses' => '',
                    ],
                    [
                        'name' => 'Name',
                        'field' => 'name',
                        'columnClasses' => '',
                        'rowClasses' => '',
                    ],
                ]" :rows="$available_weapons">
                    <x-slot name="tableActions">
                        <x-primary-button
                            x-on:click.prevent="weaponFormId = row.id; weaponFormName = row.name; $dispatch('close-modal', 'weapon-form-modal')">
                            <span>{{ __('nations.select') }}</span>
                        </x-primary-button>
                    </x-slot>
                </x-table>
            </div>
        </x-modal>
    @endif

</div>
