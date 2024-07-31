@props(['athletes' => [], 'academy' => null])

@php
    $authRole = auth()->user()->getRole();
    $addToRoute = $authRole === 'admin' ? '' : $authRole . '.';
@endphp

<div x-data="{ selectedUserId: null }">
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'athlete-modal')">
        <span>{{ __('academies.associate_athletes') }}</span>
    </x-primary-button>

    <x-modal name="athlete-modal" :show="$errors->userId->isNotEmpty()" focusable>
        <form method="post" action="{{ route($addToRoute . 'academies.athlete.store', $academy->id) }}" class="p-6" x-ref="form">
            @csrf

            <input type="hidden" name="athlete_id" x-model="selectedUserId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('academies.associate_athletes') }}
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
                        x-on:click="selectedUserId = row.id; $nextTick(() => { $refs.form.submit(); })">
                        <span>{{ __('nations.select') }}</span>
                    </x-primary-button>
                </x-slot>
            </x-table>

        </form>
    </x-modal>
</div>
