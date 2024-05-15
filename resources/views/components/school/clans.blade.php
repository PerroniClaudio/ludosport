@props(['clans' => [], 'school' => null])

<div x-data="{ selectedClanId: null }">
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'clan-modal')">
        <span>{{ __('school.associate_clan') }}</span>
    </x-primary-button>

    <x-modal name="clan-modal" :show="$errors->schoolId->isNotEmpty()" focusable>
        <form method="post" action="{{ route('schools.clans.store', $school->id) }}" class="p-6" x-ref="form">
            @csrf

            <input type="hidden" name="clan_id" x-model="selectedClanId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('school.associate_clan') }}
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
            ]" :rows="$clans">
                <x-slot name="tableRows">
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                        x-text="row.id"></td>
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                        x-text="row.name"></td>
                    <td
                        class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                        <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'clan-modal')"
                            x-on:click="selectedClanId = row.id; $nextTick(() => { $refs.form.submit(); })">
                            <span>{{ __('nations.select') }}</span>
                        </x-primary-button>
                    </td>
                </x-slot>

            </x-table>
        </form>
    </x-modal>
</div>
