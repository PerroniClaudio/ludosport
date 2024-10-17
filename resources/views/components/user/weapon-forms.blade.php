@props([
    'forms' => [],
    'isPersonnel' => false,
    'availableWeaponForms' => [],
    'user' => null,
])
@php
    $authRole = auth()->user()->getRole();
@endphp
<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8" x-data={}>
    <div class="flex justify-between">
        <h3 class="text-background-800 dark:text-background-200 text-2xl">
            @if ($isPersonnel)
                {{ __('users.weapons_forms_personnel') }}
            @else
                {{ __('users.weapons_forms') }}</h3>
            @endif
        </h3>
        @if ($authRole === 'admin')
            @if($isPersonnel)
                <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', 'add-weapon-form-personnel-modal')">
                    <x-lucide-plus class="w-5 h-5 text-white" />
                </x-primary-button>
            @else
                <x-primary-button type="button" x-on:click.prevent="$dispatch('open-modal', 'add-weapon-form-modal')">
                    <x-lucide-plus class="w-5 h-5 text-white" />
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

    </x-table>
    
    @if ($authRole === 'admin')
        @if($isPersonnel)
            <x-modal name="add-weapon-form-personnel-modal" :show="$errors->customrole->isNotEmpty()" focusable>
                <div class="p-6 flex flex-col gap-2" x-data="{
                    weapon_forms: {{ collect($availableWeaponForms) }},
                    userWeaponForms: {{ collect($forms) }},
                    selectedWeaponForms: {{ collect($forms) }},
                    shouldShowWeaponForm(id) {
                        return !this.userWeaponForms.find(weaponForm => weaponForm.id === id) && !this.selectedWeaponForms.find(weaponForm => weaponForm.id === id);
                    },
                    addLanguage(weaponForm) {
                        this.selectedWeaponForms.push(weaponForm);
                    },
                    removeLanguage(weaponForm) {
                        this.selectedWeaponForms = this.selectedWeaponForms.filter(selectedLanguage => selectedLanguage.id !== weaponForm.id);
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
                                <div x-show="shouldShowWeaponForm(weaponForm.id)" x-on:click="addLanguage(weaponForm)"
                                    class="p-2 border border-background-100 dark:border-background-700 rounded-lg cursor-pointer">
                                    <p class="text-background-500 dark:text-background-300" x-text="weaponForm.name"></p>
                                </div>
                            </template>
                        </div>

                    </div>

                    <div class="mt-4" x-show="selectedWeaponForms.length > 0">
                        <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                            {{ __('users.selected_weapon_forms') }}</h4>

                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="weaponForm in selectedWeaponForms" :key="weaponForm.id">
                                <div x-on:click="removeLanguage(weaponForm)"
                                    class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer">
                                    <p class="text-primary-500" x-text="weaponForm.name"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button type="button" @click="associateWeaponForms">
                            <span>{{ __('users.weapon_forms_edit') }}</span>
                        </x-primary-button>
                    </div>
                </div>
            </x-modal>
        @else
            <x-modal name="add-weapon-form-modal" :show="$errors->customrole->isNotEmpty()" focusable>
                <div class="p-6 flex flex-col gap-2" x-data="{
                    weapon_forms: {{ collect($availableWeaponForms) }},
                    userWeaponForms: {{ collect($forms) }},
                    selectedWeaponForms: {{ collect($forms) }},
                    shouldShowWeaponForm(id) {
                        return !this.userWeaponForms.find(weaponForm => weaponForm.id === id) && !this.selectedWeaponForms.find(weaponForm => weaponForm.id === id);
                    },
                    addLanguage(weaponForm) {
                        this.selectedWeaponForms.push(weaponForm);
                    },
                    removeLanguage(weaponForm) {
                        this.selectedWeaponForms = this.selectedWeaponForms.filter(selectedLanguage => selectedLanguage.id !== weaponForm.id);
                    },
                    associateWeaponForms() {
                        const weapon_forms = this.selectedWeaponForms.map(weaponForm => weaponForm.id);
                
                        const formData = new FormData();
                        formData.append('weapon_forms', weapon_forms);
                
                        fetch('/users/{{ $user }}/weapon-forms-athlete', {
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
                                <div x-show="shouldShowWeaponForm(weaponForm.id)" x-on:click="addLanguage(weaponForm)"
                                    class="p-2 border border-background-100 dark:border-background-700 rounded-lg cursor-pointer">
                                    <p class="text-background-500 dark:text-background-300" x-text="weaponForm.name"></p>
                                </div>
                            </template>
                        </div>

                    </div>

                    <div class="mt-4" x-show="selectedWeaponForms.length > 0">
                        <h4 class="text-md font-medium text-background-900 dark:text-background-100">
                            {{ __('users.selected_weapon_forms') }}</h4>

                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="weaponForm in selectedWeaponForms" :key="weaponForm.id">
                                <div x-on:click="removeLanguage(weaponForm)"
                                    class="p-2 border border-primary-500 dark:border-primary-500 rounded-lg cursor-pointer">
                                    <p class="text-primary-500" x-text="weaponForm.name"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button type="button" @click="associateWeaponForms">
                            <span>{{ __('users.weapon_forms_edit') }}</span>
                        </x-primary-button>
                    </div>
                </div>
            </x-modal>
        @endif
    @endif

</div>
