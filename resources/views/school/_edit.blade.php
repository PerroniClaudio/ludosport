<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('school.edit') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('school.info') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                <form method="POST" action="{{ route('schools.update', $school->id) }}">
                    @csrf
                    <div class="flex flex-col gap-2 w-1/2">
                        <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                            :value="$school->name" placeholder="{{ fake()->company() }}" />
                        <x-school.academy nationality="{{ $school->nation_id }}"
                            selectedAcademyId="{{ $school->academy_id }}" selectedAcademy="{{ $school->academy->name }}"
                            :nations="$nations" :academies="$academies" />
                    </div>

                    <h1 class="text-background-800 dark:text-background-200 text-lg">{{ __('academies.address') }}</h1>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="flex flex-col gap-2 w-1/2">
                        <x-form.input name="address" label="Address" type="text" required="{{ true }}"
                            value="{{ $school->address }}" placeholder="{{ fake()->address() }}" />
                        <x-form.input name="city" label="City" type="text" required="{{ true }}"
                            value="{{ $school->city }}" placeholder="{{ fake()->city() }}" />
                        <x-form.input name="zip" label="Zip" type="text" required="{{ true }}"
                            value="{{ $school->zip }}" placeholder="{{ fake()->postcode() }}" />
                    </div>


                    <div class="fixed bottom-8 right-32">
                        <x-primary-button type="submit">
                            <x-lucide-save class="w-6 h-6 text-white" />
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <x-school.search-users :school="$school" :roles="$roles" />

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('academies.personnel') }}
                    </h3>
                    <div class="flex items-center gap-1">
                        <x-school.personnel :school="$school" :personnel="$personnel" />
                        <x-school.create-user :school="$school->id" type="personnel" :roles="$editable_roles" />
                    </div>

                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
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
                    [
                        'name' => 'Surname',
                        'field' => 'surname',
                        'columnClasses' => '',
                        'rowClasses' => '',
                    ],
                    [
                        'name' => 'Role',
                        'field' => 'role',
                        'columnClasses' => '',
                        'rowClasses' => '',
                    ],
                ]" :rows="$associated_personnel">
                    <x-slot name="tableActions">
                        <a x-bind:href="'/users/' + row.id">
                            <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                        </a>
                    </x-slot>
                </x-table>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('academies.athletes') }}
                    </h3>
                    <div class="flex items-center gap-1">
                        <x-school.athletes :school="$school" :athletes="$athletes" />
                        <x-school.create-user :school="$school->id" type="athlete" />
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
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
                    [
                        'name' => 'Surname',
                        'field' => 'surname',
                        'columnClasses' => '',
                        'rowClasses' => '',
                    ],
                    [
                        'name' => 'Enabled',
                        'field' => 'is_disabled',
                        'columnClasses' => '',
                        'rowClasses' => '',
                    ],
                    [
                        'name' => 'Actions',
                        'field' => 'actions',
                        'columnClasses' => 'text-right',
                        'rowClasses' => '',
                    ],
                ]" :rows="$associated_athletes">
                    <x-slot name="tableRows">
                        <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.id"></td>
                        <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.name"></td>
                        <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.surname"></td>
                        <td
                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                            <div x-show="row.is_disabled">
                                <x-lucide-x-circle class="w-5 h-5 text-red-500 dark:text-red-400" />
                            </div>
                            <div x-show="!row.is_disabled">
                                <x-lucide-check-circle class="w-5 h-5 text-green-500 dark:text-green-400" />
                            </div>
                        </td>
                        <td
                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                            <a x-bind:href="'/users/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </td>
                    </x-slot>
                </x-table>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('school.clans') }}
                    </h3>
                    <div class="flex items-center gap-1">
                        {{-- <x-school.clans :school="$school" :athletes="$clans" /> --}}
                        <x-school.create-clan :school="$school->id" />
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
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
                ]" :rows="$school->clan">
                    <x-slot name="tableActions">
                        <a x-bind:href="'/courses/' + row.id">
                            <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                        </a>
                    </x-slot>
                </x-table>
            </div>


            @if (!$school->is_disabled)
                <x-school.disable-form :school="$school->id" />
            @endif
        </div>
    </div>
</x-app-layout>
