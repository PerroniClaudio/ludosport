<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('clan.edit') }} #{{ $clan->id }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form method="POST" action="{{ route('rector.clans.update', $clan->id) }}">
                    @csrf
                    <div class="flex flex-col gap-2 w-1/2">
                        <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                            value="{{ $clan->name }}" placeholder="{{ fake()->company() }}" />

                        {{-- <x-clan.school :selectedSchoolId="$clan->school_id" :selectedSchool="$clan->school->name" /> --}}
                        <x-clan.rector.school :selectedSchoolId="$clan->school_id" :selectedSchool="$clan->school->name" />

                    </div>

                    <div class="fixed bottom-8 right-32">
                        <x-primary-button type="submit">
                            <x-lucide-save class="w-6 h-6 text-white" />
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('clan.instructors') }}
                    </h3>
                    <div class="flex items-center gap-1">
                        <x-clan.instructors :clan="$clan" :instructors="$instructors" />
                        <x-clan.create-user :clan="$clan->id" type="personnel" :roles="$roles" />
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
                ]" :rows="$associated_instructors">
                    <x-slot name="tableActions">
                        <a x-bind:href="'/rector/users/' + row.id">
                            <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                        </a>
                    </x-slot>
                </x-table>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('clan.athletes') }}
                    </h3>
                    <div class="flex items-center gap-1">
                        <x-clan.athletes :clan="$clan" :athletes="$athletes" />
                        <x-clan.create-user :clan="$clan->id" type="athlete" />
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
                            <a x-bind:href="'/rector/users/' + row.id">
                                <x-lucide-pencil
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </td>

                    </x-slot>
                </x-table>
            </div>


            @if (!$clan->is_disabled)
                <x-clan.disable-form :clan="$clan->id" />
            @endif

        </div>
    </div>
</x-app-layout>
