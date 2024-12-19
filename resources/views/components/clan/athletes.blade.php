@props(['athletes' => [], 'clan' => null, 'associatedAthletes' => []])

<div x-data="{ selectedUserId: null }">
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'athletes-modal')">
        <span>{{ __('clan.associate_athletes') }}</span>
    </x-primary-button>

    <x-modal name="athletes-modal" :show="$errors->userId->isNotEmpty()" focusable>
        @php
            $authRole = auth()->user()->getRole();
            $addFormRoute = $authRole === 'admin' ? 'clans.athletes.store' : $authRole . '.clans.athletes.store';
            $removeFormRoute = $authRole === 'admin' ? 'clans.athletes.remove' : $authRole . '.clans.athletes.remove';
        @endphp
        <form method="post" action="{{ route($addFormRoute, $clan->id) }}" class="p-6" x-ref="addform">
            @csrf

            <input type="hidden" name="athlete_id" x-model="selectedUserId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('clan.associate_athletes') }}
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
            ]" :rows="$athletes">
                <x-slot name="tableActions">
                    <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'athlete-modal')"
                        x-on:click="selectedUserId = row.id; $nextTick(() => { $refs.addform.submit(); })">
                        <span>{{ __('clan.select') }}</span>
                    </x-primary-button>
                </x-slot>
            </x-table>

        </form>
        
        <form method="post" action="{{ route($removeFormRoute, $clan->id) }}" class="p-6" x-ref="removeform">
            @csrf

            <input type="hidden" name="athlete_id" x-model="selectedUserId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('clan.remove_athletes') }}
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
            ]" :rows="$associatedAthletes">
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
