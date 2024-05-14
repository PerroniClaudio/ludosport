@props(['academies' =>[], 'nation' => null])

<div
    x-data="{ selectedAcademyId: null }"
>
    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'academies-modal')">
        <span>{{ __('nations.associate_academy') }}</span>
    </x-primary-button>

    <x-modal name="academies-modal" :show="$errors->academyId->isNotEmpty()" focusable>
        <form method="post" action="{{ route('nations.academies.store', $nation->id) }}" class="p-6" x-ref="form">
            @csrf

            <input type="hidden" name="academy_id" x-model="selectedAcademyId">

            <h2 class="text-lg font-medium text-background-900 dark:text-background-100">
                {{ __('nations.associate_academy') }}
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
                :rows="$academies"
            >
                <x-slot name="tableRows">
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.id"></td>
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.name"></td>
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.nation.name"></td>
                    <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                        <x-primary-button x-on:click.prevent="$dispatch('open-modal', 'academies-modal')" x-on:click="selectedAcademyId = row.id; $nextTick(() => { $refs.form.submit(); })">
                            <span>{{ __('nations.select') }}</span>
                        </x-primary-button>
                    </td>
                </x-slot> 
   
            </x-table>
        </form>
    </x-modal>
</div>