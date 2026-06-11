<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('weaponf.edit_weapon_form', ['id' => $weaponForm->id]) }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12" x-data="{
        updateWeaponFormType: '',
        updateWeaponFormDate: '',
        userId: '',

        openEditWeaponFormModal(type, date, userId) {
            console.log(type, date, userId);
            this.userId = userId;
            this.updateWeaponFormType = type;
            this.updateWeaponFormDate = date.split(' ')[0];
            {{-- $dispatch('open-modal', 'edit-' + type + '-weapon-form-date-modal'); --}}
            $dispatch('open-modal', 'edit-weapon-form-date-modal');
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4" >
            <div class="grid grid-cols-2 gap-4">

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <form method="POST" action="{{ route('weapon-forms.update', $weaponForm->id) }}"
                        class="p-6 text-background-900 dark:text-background-100">
                        @csrf
                        <div class="flex items-center justify-between">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('weaponf.info') }}
                            </h3>

                        </div>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                        <x-form.input label="Name" name="name" :value="$weaponForm->name" />

                        @if (!$weaponForm->isDefaultLayoutForm())
                            <div class="mt-4">
                                @include('weapon-forms._position-fields', [
                                    'selectedPosition' => collect(\App\Models\WeaponForm::POSITION_FIELDS)->first(fn ($field) => $weaponForm->{$field}),
                                ])
                            </div>
                        @else
                            <div class="mt-4 rounded-lg border border-background-100 dark:border-background-700 p-4">
                                <p class="text-sm text-background-600 dark:text-background-300">
                                    Hardcoded default form. Position handled by existing layout.
                                </p>
                            </div>
                        @endif


                        <div class="fixed bottom-8 right-32">
                            <x-primary-button type="submit">
                                <x-lucide-save class="w-6 h-6 text-white" />
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    x-data="{}">
                    <div class="flex justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">Weapon form assets
                        </h3>
                        <form method="POST" action="{{ route('weapon-forms.image.update', $weaponForm->id) }}"
                            enctype="multipart/form-data" x-ref="weaponFormAssetUpload">
                            @csrf
                            @method('PUT')
                            <input type="file" name="weaponformlogo" id="weaponformlogo" class="hidden"
                                accept=".svg,image/svg+xml" x-on:change="$refs.weaponFormAssetUpload.submit()" />
                            <x-primary-button type="button"
                                onclick="document.getElementById('weaponformlogo').click()">
                                Upload SVG
                            </x-primary-button>
                        </form>
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <p class="text-sm text-background-600 dark:text-background-300 mb-4">
                        Upload one SVG. Same file will be saved for athlete, instructor, technician, default.
                    </p>

                    @php
                        $assetVariants = [
                            'athlete' => 'Athlete form',
                            'instructor' => 'Instructor form',
                            'technician' => 'Technician form',
                            'default' => 'Default form',
                        ];
                    @endphp

                    <div class="grid grid-cols-1 gap-4">
                        @foreach ($assetVariants as $assetVariant => $assetLabel)
                            <div class="rounded-lg border border-background-200 dark:border-background-700 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <h4 class="text-lg font-semibold text-background-800 dark:text-background-200">
                                            {{ $assetLabel }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-center rounded-lg border border-background-300 dark:border-background-700 bg-slate-700 dark:bg-slate-900 p-4">
                                    <img src="{{ route('weapon-form-variant-image', ['weapon' => $weaponForm->id, 'variant' => $assetVariant]) }}"
                                        alt="{{ $weaponForm->name }} {{ $assetVariant }}" class="h-20 w-20">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('weaponf.technicians') }}</h3>
                        <x-weapon-forms.select-technicians weapon_form_id="{{ $weaponForm->id }}" :technicians="$technicians_to_add" />
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
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
                            'name' => 'Awarded on',
                            'field' => 'awarded_on',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$technicians">
                        <x-slot name="tableActions" >
                            <div class="flex gap-4">
                                <button type="button" x-on:click.prevent="()=>openEditWeaponFormModal('technician', row.awarded_at, row.id)">
                                    <x-lucide-pencil
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                <button type="button">
                                <a x-bind:href="'/users/' + row.id">
                                    <x-lucide-arrow-right
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                </a>
                            </div>
                        </x-slot>
                    </x-table>
                </div>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('weaponf.instructors') }}</h3>
                        <x-weapon-forms.select-instructors weapon_form_id="{{ $weaponForm->id }}" :personnel="$personnel" />
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
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
                            'name' => 'Awarded on',
                            'field' => 'awarded_on',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$instructors">
                        <x-slot name="tableActions">
                            <div class="flex gap-4">
                                <button type="button" x-on:click.prevent="()=>openEditWeaponFormModal('personnel', row.awarded_at, row.id)">
                                    <x-lucide-pencil
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                <button type="button">
                                <a x-bind:href="'/users/' + row.id">
                                    <x-lucide-arrow-right
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                </a>
                            </div>
                        </x-slot>
                    </x-table>
                </div>
            </div>
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('weaponf.athletes') }}</h3>
                        <x-weapon-forms.select-athlete weapon_form_id="{{ $weaponForm->id }}" :athletes="$athletes_to_add" />

                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
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
                            'name' => 'Awarded on',
                            'field' => 'awarded_on',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$athletes">
                        <x-slot name="tableActions">
                            <div class="flex gap-4">
                                <button type="button" x-on:click.prevent="()=>openEditWeaponFormModal('athlete', row.awarded_at, row.id)">
                                    <x-lucide-pencil
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                <button type="button">
                                <a x-bind:href="'/users/' + row.id">
                                    <x-lucide-arrow-right
                                        class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                </a>
                            </div>
                        </x-slot>
                    </x-table>
                </div>
            </div>
        </div>

        <x-modal name="edit-weapon-form-date-modal" :show="false" focusable
            className="p-6 flex flex-col gap-2"
        >
            <form x-bind:action="'/users/' + userId + '/weapon-forms-edit-date'" method="POST" class="p-6 flex flex-col gap-4">
                @csrf
                <input type="text" name="type" id="type" x-bind:value="updateWeaponFormType" hidden >
                <input type="text" name="form_id" id="form_id" x-bind:value="{{$weaponForm->id}}" hidden >
                <x-input-label value="Awarded on" for="awarded-at'" />
                <input type="date" name="awarded_at" id="awarded-at" x-bind:value="updateWeaponFormDate"
                    class="disabled:cursor-not-allowed w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm"
                >

                <div class="flex justify-end">
                    <x-primary-button type="submit">
                        <span>{{ __('users.weapon_forms_edit') }}</span>
                    </x-primary-button>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>
