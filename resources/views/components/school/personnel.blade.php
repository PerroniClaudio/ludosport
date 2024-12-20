@props(['personnel' => [], 'school' => null, 'associatedPersonnel' => []])

<div x-data="{ selectedUserId: null }">
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'personnel-modal')">
        <span>{{ __('school.associate_personnel') }}</span>
    </x-primary-button>

    <x-modal name="personnel-modal" :show="$errors->userId->isNotEmpty()" focusable>
        @php
            $authRole = auth()->user()->getRole();
            $addFormRoute = $authRole === 'admin' ? 'schools.personnel.store' : $authRole . '.schools.personnel.store';
            $removeFormRoute = $authRole === 'admin' ? 'schools.personnel.remove' : $authRole . '.schools.personnel.remove';
        @endphp
        <form method="post" action="{{ route($addFormRoute, $school->id) }}" class="p-6" x-ref="addform">
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
                        x-on:click="selectedUserId = row.id; $nextTick(() => { $refs.addform.submit(); })">
                        <span>{{ __('school.select') }}</span>
                    </x-primary-button>
                </x-slot>
            </x-table>

        </form>
        
        <form method="post" action="{{ route($removeFormRoute, $school->id) }}" class="p-6" x-ref="removeform">
            @csrf

            <input type="hidden" name="personnel_id" x-model="selectedUserId">

            <div class="flex gap-2 items-center">
                <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                    {{ __('school.remove_personnel') }}
                </h2>
                <div class='has-tooltip'>
                    <span class='tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full'>
                        {{ __('school.remove_personnel_tooltip') }}
                    </span>
                    <x-lucide-info class="h-4 text-background-400" />
                </div>
            </div>

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
            ]" :rows="$associatedPersonnel">
                <x-slot name="tableActions">
                    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'personnel-modal')"
                        x-on:click="selectedUserId = row.id; $nextTick(() => { $refs.removeform.submit(); })">
                        <span>{{ __('school.remove') }}</span>
                    </x-primary-button>
                </x-slot>
            </x-table>

        </form>
    </x-modal>
</div>
