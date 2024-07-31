@props(['schools' =>[], 'academy' => null])

@php
    $authRole = auth()->user()->getRole();
    $addToRoute = $authRole === 'admin' ? '' : $authRole . '.';
@endphp

<div
    x-data="{ selectedSchoolId: null }"
>
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'school-modal')">
        <span>{{ __('academies.associate_school') }}</span>
    </x-primary-button>

    <x-modal name="school-modal" :show="$errors->schoolId->isNotEmpty()" focusable>
        <form method="post" action="{{ route($addToRoute . 'academies.schools.store', $academy->id) }}" class="p-6" x-ref="form">
            @csrf

            <input type="hidden" name="school_id" x-model="selectedSchoolId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('academies.associate_school') }}
            </h2>
            
            <x-table 
                striped="false"
                :columns="[
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
                        'name' => 'Nation',
                        'field' => 'nation',
                        'columnClasses' => '',
                        'rowClasses' => '', 
                    ],
                    [
                        'name' => 'Action',
                        'field' => '',
                        'columnClasses' => '',
                        'rowClasses' => '', 
                    ],
                ]"
                :rows="$schools"
            >
                <x-slot name="tableRows">
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.id"></td>
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.name"></td>
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.nation.name"></td>
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                        <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'school-modal')" x-on:click="selectedSchoolId = row.id; $nextTick(() => { $refs.form.submit(); })">
                            <span>{{ __('nations.select') }}</span>
                        </x-primary-button>
                    </td>
                </x-slot> 
   
            </x-table>
        </form>
    </x-modal>
</div>