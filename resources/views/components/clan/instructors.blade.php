@props(['instructors' => [], 'clan' => null, 'associatedInstructors' => []])

<div x-data="{ selectedUserId: null }">
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'instructors-modal')">
        <span>{{ __('clan.associate_instructors') }}</span>
    </x-primary-button>

    <x-modal name="instructors-modal" :show="$errors->userId->isNotEmpty()" focusable>
        @php
            $authRole = auth()->user()->getRole();
            $addFormRoute = $authRole === 'admin' ? 'clans.instructors.store' : $authRole . '.clans.instructors.store';
            $removeFormRoute = $authRole === 'admin' ? 'clans.instructors.remove' : $authRole . '.clans.instructors.remove';
        @endphp
        <form method="post" action="{{ route($addFormRoute, $clan->id) }}" class="p-6" x-ref="addform">
            @csrf

            <input type="hidden" name="instructor_id" x-model="selectedUserId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('clan.associate_instructors') }}
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
            ]" :rows="$instructors">
                <x-slot name="tableActions">
                    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'athlete-modal')"
                        x-on:click="selectedUserId = row.id; $nextTick(() => { $refs.addform.submit(); })">
                        <span>{{ __('nations.select') }}</span>
                    </x-primary-button>
                </x-slot>
            </x-table>

        </form>
        
        <form method="post" action="{{ route($removeFormRoute, $clan->id) }}" class="p-6" x-ref="removeform">
            @csrf

            <input type="hidden" name="instructor_id" x-model="selectedUserId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('clan.remove_instructors') }}
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
            ]" :rows="$associatedInstructors">
                <x-slot name="tableActions">
                    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'athlete-modal')"
                        x-on:click="selectedUserId = row.id; $nextTick(() => { $refs.removeform.submit(); })">
                        <span>{{ __('clan.remove') }}</span>
                    </x-primary-button>
                </x-slot>
            </x-table>

        </form>
    </x-modal>
</div>
