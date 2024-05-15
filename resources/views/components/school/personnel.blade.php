@props(['personnel' => [], 'school' => null])

<div x-data="{ selectedUserId: null }">
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'personnel-modal')">
        <span>{{ __('school.associate_personnel') }}</span>
    </x-primary-button>

    <x-modal name="personnel-modal" :show="$errors->userId->isNotEmpty()" focusable>
        <form method="post" action="{{ route('schools.personnel.store', $school->id) }}" class="p-6" x-ref="form">
            @csrf

            <input type="hidden" name="personnel_id" x-model="selectedUserId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('school.associate_personnel') }}
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
            ]" :rows="$personnel">
                <x-slot name="tableActions">
                    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'personnel-modal')"
                        x-on:click="selectedUserId = row.id; $nextTick(() => { $refs.form.submit(); })">
                        <span>{{ __('nations.select') }}</span>
                    </x-primary-button>
                </x-slot>
            </x-table>

        </form>
    </x-modal>
</div>
