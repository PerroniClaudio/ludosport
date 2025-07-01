@props(['academies' => [], 'nation' => null])

<div x-data="{ selectedAcademyId: null, selectedAcademy: 'Select an academy' }">
    <x-input-label for="academy" value="{{ __('users.academy') }}" />
    <div class="flex w-full gap-2">
        <x-text-input disabled name="academy" class="flex-1" type="text" x-model="selectedAcademy" />
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'academies-modal')">
            <x-lucide-search class="w-6 h-6 text-white" />
        </x-primary-button>
    </div>

    <x-modal name="academies-modal" :show="$errors->academyId->isNotEmpty()" focusable>
        <div class="p-6">

            <input type="hidden" name="academy_id" x-model="selectedAcademyId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('events.select_academy') }}
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
            
                [
                    'name' => 'Action',
                    'field' => '',
                    'columnClasses' => '',
                    'rowClasses' => '',
                ],
            ]" :rows="$academies">
                <x-slot name="tableRows">
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                        x-text="row.id"></td>
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                        x-text="row.name"></td>

                    <td
                        class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                        <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'academies-modal')"
                            x-on:click="selectedAcademyId = row.id; selectedAcademy = row.name; $dispatch('close')">
                            <span>{{ __('nations.select') }}</span>
                        </x-primary-button>
                    </td>
                </x-slot>

            </x-table>
        </div>
    </x-modal>
</div>
