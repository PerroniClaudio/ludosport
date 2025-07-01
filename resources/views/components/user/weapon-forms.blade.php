@props([
    'forms' => [],
    // 'isPersonnel' => false,
    'type' => 'athlete',
    'availableWeaponForms' => [],
    'user' => null,
])
@php
    $authRole = auth()->user()->getRole();
@endphp
<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-data="{
    updateWeaponFormId: '',
    updateWeaponFormDate: '',
    openEditWeaponFormModal(type, id, date) {
        this.updateWeaponFormDate = date.split(' ')[0];
        this.updateWeaponFormId = id;
        $dispatch('open-modal', 'edit-' + type + '-weapon-form-date-modal');
    }

}">
    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">
            @if ($type === 'technician')
                {{ __('users.weapons_forms_technician') }}
            @elseif ($type === 'personnel')
                {{ __('users.weapons_forms_personnel') }}
            @else
                {{ __('users.weapons_forms') }}
        </h3>
        @endif
        </h3>
        {{-- l'admin può modificare tutto. il rettore solo le forme da atleta --}}
        @if ($type === 'technician')
            @if ($authRole === 'admin')
                <x-primary-button type="button" class="h-fit"
                    x-on:click.prevent="$dispatch('open-modal', 'add-weapon-form-technician-modal')">
                    <x-lucide-plus class="w-5 h-5" />
                </x-primary-button>
            @endif
        @elseif($type === 'personnel')
            @if ($authRole === 'admin')
                <x-primary-button type="button" class="h-fit"
                    x-on:click.prevent="$dispatch('open-modal', 'add-weapon-form-personnel-modal')">
                    <x-lucide-plus class="w-5 h-5" />
                </x-primary-button>
            @endif
        @else
            @if (in_array($authRole, ['admin', 'rector', 'dean', 'manager']))
                <x-primary-button type="button" class="h-fit"
                    x-on:click.prevent="$dispatch('open-modal', 'add-weapon-form-modal')">
                    <x-lucide-plus class="w-5 h-5" />
                </x-primary-button>
            @endif
        @endif
    </div>
    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
    <x-table striped="false" :columns="[
        [
            'name' => 'Name',
            'field' => 'name',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
        [
            'name' => 'Awarded on',
            'field' => 'awarded_at',
            'columnClasses' => '', // classes to style table th
            'rowClasses' => '', // classes to style table td
        ],
    ]" :rows="$forms">

        <x-slot name="tableActions">
            <button type="button"
                x-on:click.prevent="()=>openEditWeaponFormModal('{{ $type }}', row.id, row.awarded_at)">
                <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
            </button>
        </x-slot>

    </x-table>

    @if ($type === 'technician')
        @if ($authRole === 'admin')
            <x-modal name="add-weapon-form-technician-modal" :show="$errors->customrole->isNotEmpty()" focusable>
                <div class="p-6 flex flex-col gap-2" x-data="{
                    weapon_forms: {{ collect($availableWeaponForms) }},
                    userWeaponForms: {{ collect($forms) }},
                    selectedWeaponForms: {{ collect($forms) }},
                    shouldShowWeaponForm(id) {
                        {{-- return !this.userWeaponForms.find(weaponForm => weaponForm.id === id) && !this.selectedWeaponForms.find(weaponForm => weaponForm.id === id); --}}
                        return !this.selectedWeaponForms.find(weaponForm => weaponForm.id === id);
                    },
                    addWeaponForm(weaponForm) {
                        this.selectedWeaponForms.push(weaponForm);
                    },
                    removeWeaponForm(weaponForm) {
                        this.selectedWeaponForms = this.selectedWeaponForms.filter(selectedWeaponForm => selectedWeaponForm.id !== weaponForm.id);
                    },
                    associateWeaponForms() {
                        const weapon_forms = this.selectedWeaponForms.map(weaponForm => weaponForm.id);
                
                        const formData = new FormData();
                        formData.append('weapon_forms', weapon_forms);
                
                        fetch('/users/{{ $user }}/weapon-forms-technician', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData,
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data)
                                window.location.reload();
                            })
                    },
                }">
                    <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                        {{ __('users.weapon_forms_add') }}
                    </h2>
                    <div>

                        <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                            {{ __('users.available_weapon_forms') }}</h4>

                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="weaponForm in weapon_forms" :key="weaponForm.id">
                                <div x-show="shouldShowWeaponForm(weaponForm.id)" x-on:click="addWeaponForm(weaponForm)"
                                    class="p-2 border border-background-100 dark:border-background-700 rounded-lg cursor-pointer">
                                    <p class="text-sm text-background-500 dark:text-background-300"
                                        x-text="weaponForm.name"></p>
                                </div>
                            </template>
                        </div>

                    </div>
                    <template x-if="selectedWeaponForms && (selectedWeaponForms.length > 0)">
                        <div class="mt-4">
                            <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                                {{ __('users.selected_weapon_forms') }}</h4>

                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="weaponForm in selectedWeaponForms" :key="weaponForm.id">
                                    <div x-on:click="removeWeaponForm(weaponForm)"
                                        class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer">
                                        <p class="text-sm text-primary-500" x-text="weaponForm.name"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div class="flex justify-end">
                        <x-primary-button type="button" @click="associateWeaponForms">
                            <span>{{ __('users.weapon_forms_edit') }}</span>
                        </x-primary-button>
                    </div>
                </div>
            </x-modal>
        @endif
    @elseif($type === 'personnel')
        @if ($authRole === 'admin')
            <x-modal name="add-weapon-form-personnel-modal" :show="$errors->customrole->isNotEmpty()" focusable>
                <div class="p-6 flex flex-col gap-2" x-data="{
                    weapon_forms: {{ collect($availableWeaponForms) }},
                    userWeaponForms: {{ collect($forms) }},
                    selectedWeaponForms: {{ collect($forms) }},
                    shouldShowWeaponForm(id) {
                        {{-- return !this.userWeaponForms.find(weaponForm => weaponForm.id === id) && !this.selectedWeaponForms.find(weaponForm => weaponForm.id === id); --}}
                        return !this.selectedWeaponForms.find(weaponForm => weaponForm.id === id);
                    },
                    addWeaponForm(weaponForm) {
                        this.selectedWeaponForms.push(weaponForm);
                    },
                    removeWeaponForm(weaponForm) {
                        this.selectedWeaponForms = this.selectedWeaponForms.filter(selectedWeaponForm => selectedWeaponForm.id !== weaponForm.id);
                    },
                    associateWeaponForms() {
                        const weapon_forms = this.selectedWeaponForms.map(weaponForm => weaponForm.id);
                
                        const formData = new FormData();
                        formData.append('weapon_forms', weapon_forms);
                
                        fetch('/users/{{ $user }}/weapon-forms-personnel', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData,
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data)
                                window.location.reload();
                            })
                    },
                }">
                    <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                        {{ __('users.weapon_forms_add') }}
                    </h2>
                    <div>

                        <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                            {{ __('users.available_weapon_forms') }}</h4>

                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="weaponForm in weapon_forms" :key="weaponForm.id">
                                <div x-show="shouldShowWeaponForm(weaponForm.id)" x-on:click="addWeaponForm(weaponForm)"
                                    class="p-2 border border-background-100 dark:border-background-700 rounded-lg cursor-pointer">
                                    <p class="text-sm text-background-500 dark:text-background-300"
                                        x-text="weaponForm.name"></p>
                                </div>
                            </template>
                        </div>

                    </div>

                    <template x-if="selectedWeaponForms && (selectedWeaponForms.length > 0)">
                        <div class="mt-4">
                            <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                                {{ __('users.selected_weapon_forms') }}</h4>

                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="weaponForm in selectedWeaponForms" :key="weaponForm.id">
                                    <div x-on:click="removeWeaponForm(weaponForm)"
                                        class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer">
                                        <p class="text-sm text-primary-500" x-text="weaponForm.name"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-end">
                        <x-primary-button type="button" @click="associateWeaponForms">
                            <span>{{ __('users.weapon_forms_edit') }}</span>
                        </x-primary-button>
                    </div>
                </div>
            </x-modal>
        @endif
    @else
        @if (in_array($authRole, ['admin', 'rector', 'dean', 'manager']))
            {{-- l'admin può modificare tutto. il rettore solo le forme da atleta --}}
            <x-modal name="add-weapon-form-modal" :show="$errors->customrole->isNotEmpty()" focusable>
                <div class="p-6 flex flex-col gap-2" x-data="{
                    weapon_forms: {{ collect($availableWeaponForms) }},
                    userWeaponForms: {{ collect($forms) }},
                    selectedWeaponForms: {{ collect($forms) }},
                    shouldShowWeaponForm(id) {
                        {{-- return !this.userWeaponForms.find(weaponForm => weaponForm.id === id) && !this.selectedWeaponForms.find(weaponForm => weaponForm.id === id); --}}
                        return !this.selectedWeaponForms.find(weaponForm => weaponForm.id === id);
                    },
                    addWeaponForm(weaponForm) {
                        this.selectedWeaponForms.push(weaponForm);
                    },
                    removeWeaponForm(weaponForm) {
                        if ({{ $authRole === 'admin' ? 'true' : 'false' }} || !this.userWeaponForms.find(wf => wf.id == weaponForm.id)) {
                            this.selectedWeaponForms = this.selectedWeaponForms.filter(selectedWeaponForm => selectedWeaponForm.id !== weaponForm.id);
                        } else {
                            FlashMessage.displayCustomMessage('You cannot remove already associated weapon forms', 2000);
                        }
                    },
                    associateWeaponForms() {
                        const weapon_forms = this.selectedWeaponForms.map(weaponForm => weaponForm.id);
                
                        const formData = new FormData();
                        formData.append('weapon_forms', weapon_forms);
                
                        fetch('{{ $authRole === 'admin' ? '' : '/' . $authRole }}/users/{{ $user }}/weapon-forms-athlete', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData,
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log(data)
                                window.location.reload();
                            })
                    },
                }">
                    <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                        {{ __('users.weapon_forms_add') }}
                    </h2>
                    <div>

                        <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                            {{ __('users.available_weapon_forms') }}</h4>

                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="weaponForm in weapon_forms" :key="weaponForm.id">
                                <div x-show="shouldShowWeaponForm(weaponForm.id)" x-on:click="addWeaponForm(weaponForm)"
                                    class="p-2 border border-background-100 dark:border-background-700 rounded-lg cursor-pointer">
                                    <p class="text-sm text-background-500 dark:text-background-300"
                                        x-text="weaponForm.name"></p>
                                </div>
                            </template>
                        </div>

                    </div>

                    <template x-if="selectedWeaponForms && (selectedWeaponForms.length > 0)">
                        <div class="mt-4">
                            <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                                {{ __('users.selected_weapon_forms') }}</h4>

                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="weaponForm in selectedWeaponForms" :key="weaponForm.id">
                                    <div x-on:click="removeWeaponForm(weaponForm)"
                                        class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer">
                                        <p class="text-sm text-primary-500" x-text="weaponForm.name"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-end">
                        <x-primary-button type="button" @click="associateWeaponForms">
                            <span>{{ __('users.weapon_forms_edit') }}</span>
                        </x-primary-button>
                    </div>
                </div>
            </x-modal>
        @endif
    @endif

    <x-modal name="edit-{{ $type }}-weapon-form-date-modal" :show="false" focusable
        className="p-6 flex flex-col gap-2">
        <form
            action="{{ route(($authRole == 'admin' ? '' : $authRole . '.') . 'user.weapon-forms-edit-date', $user) }}"
            method="POST" class="p-6 flex flex-col gap-4">
            @csrf
            <input type="text" name="type" id="type" x-bind:value="'{{ $type }}'" hidden>
            <input type="text" name="form_id" id="form_id" x-bind:value="updateWeaponFormId" hidden>
            <x-input-label value="Awarded on" for="{{ $type }}-awarded-at" />
            <input type="date" name="awarded_at" id="{{ $type }}-awarded-at"
                x-bind:value="updateWeaponFormDate"
                class="disabled:cursor-not-allowed w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">

            <div class="flex justify-end">
                <x-primary-button type="submit">
                    <span>{{ __('users.weapon_forms_edit') }}</span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>

</div>
