<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('weaponf.title') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
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
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('academies.logo') }}
                        </h3>

                        <div>
                            <form method="POST" action="{{ route('weapon-forms.image.update', $weaponForm->id) }}"
                                enctype="multipart/form-data" x-ref="pfpform">
                                @csrf
                                @method('PUT')

                                <div class="flex flex-col gap-4">
                                    <div class="flex flex-col gap-2">
                                        <input type="file" name="weaponformlogo" id="weaponformlogo" class="hidden"
                                            x-on:change="$refs.pfpform.submit()" />
                                        <x-primary-button type="button"
                                            onclick="document.getElementById('weaponformlogo').click()">
                                            {{ __('users.upload_picture') }}
                                        </x-primary-button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <div class="flex flex-col items-center justify-center flex-1 h-full">

                        @if ($weaponForm->image)
                            <img src="{{ route('weapon-form-image', $weaponForm->id) }}" alt="{{ $weaponForm->name }}"
                                class="w-1/2 rounded-lg">
                        @endif

                    </div>
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
                            'name' => 'Type',
                            'field' => 'type',
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
                            <a x-bind:href="'/users/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
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
                            <a x-bind:href="'/users/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
