@props(['academies' => []])

@php 
    $academies = $academies->map(function($academy) {
        return [
            'id' => $academy->id,
            'name' => $academy->name,
        ];
    });
@endphp

<div x-data="{
    isDialogOpen: false,
    selectedAcademy: 'Select an academy',
    selectedAcademyId: null,
    academies: {{ $academies }},
}">
    <x-input-label for="academy" value="{{ __('users.academy') }}" />
    <div class="flex w-full gap-2">
        <input type="hidden" name="academy_id" x-model="selectedAcademyId">
        <x-text-input disabled name="academy" class="flex-1" type="text" x-model="selectedAcademy" />
        <div class="text-primary-500 hover:bg-background-500 dark:hover:bg-background-900 p-2 rounded-full cursor-pointer" x-on:click="isDialogOpen = true">
            <x-lucide-search class="w-6 h-6 text-primary-500 dark:text-primary-400" />
        
        </div>
    </div>
    <x-input-error :messages="$errors->get('academy')" class="mt-2" />

    <div
        class="modal"
        role="dialog"
        tabindex="-1"
        x-show="isDialogOpen"
        x-on:click.away="isDialogOpen = false"
        x-cloak
        x-transition
    >
        <div class="fixed inset-0 z-10 overflow-y-auto bg-black bg-opacity-50">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-background-100 dark:bg-background-800 rounded-lg shadow-lg p-6 w-full max-w-3xl">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-background-500 dark:text-background-300">{{ __('users.select_academy') }}</h2>
                        <div class="cursor-pointer" x-on:click="isDialogOpen = false">
                            <x-lucide-x class="w-6 h-6 text-background-500 dark:text-background-300" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-table striped="false" :columns="[
                            [
                                'name' => 'Id',
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
                        ]" :rows="$academies" isDialogTable="true">
                            <x-slot name="tableActions">
                                <div class="flex flex-wrap space-x-4">
                                    <x-primary-button type="button" x-on:click="selectedAcademy = row.name; selectedAcademyId = row.id; isDialogOpen = false;">{{ __('users.select') }}</x-primary-button>
                                </div>
                            </x-slot>
                        </x-table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>