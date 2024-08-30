@props([
    'event' => '',
    'results' => [],
])
@php
    $authRole = auth()->user()->getRole();
@endphp

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg" 
    x-data="enablingresults({{ $event->id }})">
    <div class="p-6 text-background-900 dark:text-background-100">
        <div class="flex justify-between">
            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.enabling_results') }}</h3>
        </div>

        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

        <x-table striped="false" :columns="[
            [
                'name' => 'User ID',
                'field' => 'user_id',
                'columnClasses' => '', // classes to style table th
                'rowClasses' => '', // classes to style table td
            ],
            [
                'name' => 'User name',
                'field' => 'user_fullname',
                'columnClasses' => '', // classes to style table th
                'rowClasses' => '', // classes to style table td
            ],
            [
                'name' => 'Weapon Form',
                'field' => 'weapon_form_name',
                'columnClasses' => '', // classes to style table th
                'rowClasses' => '', // classes to style table td]
            ],
            [
                'name' => 'Result',
                'field' => 'result',
                'columnClasses' => '', // classes to style table th
                'rowClasses' => '', // classes to style table td]
            ],
            [
                'name' => 'Stage',
                'field' => 'stage',
                'columnClasses' => '', // classes to style table th
                'rowClasses' => '', // classes to style table td]
            ],
            [
                'name' => 'Actions',
                'field' => 'actions',
                'columnClasses' => '', // classes to style table th
                'rowClasses' => '', // classes to style table td]
            ],
        ]" :rows="$results">
        @if ($authRole === 'admin')
            <x-slot name="tableRows">
                <template x-for="(column, columnIndex) in columns" :key="'column-' + columnIndex">
                    <template x-if="column.field !== 'actions'">
                        <td :class="`${column.rowClasses}`"
                            class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                            <div x-text="`${row[column.field]}`" :class="`${column.field == 'result' ? (row[column.field] == 'passed' ? 'bg-success-500' : (row[column.field] == 'review' ? 'bg-warning-500' : (row[column.field] == 'failed' ? 'bg-error-500' : 'bg-background-500'))) + ' px-1 rounded text-white' : ''}`"></div>
                        </td>
                    </template>
                </template>

                <td
                    class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                    <button @click="submitEnablingResult(row.id, 'passed')" 
                        :disabled="row.stage === 'confirmed'"
                        class="inline-flex items-center px-4 py-2 bg-success-500 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-background-50 hover:text-background-700 dark:hover:bg-background-700 focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                        Passed
                    </button>
                    <button @click="submitEnablingResult(row.id, 'failed')"
                        :disabled="row.stage === 'confirmed'"
                        class="inline-flex items-center px-4 py-2 bg-error-500 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-background-50 hover:text-background-700 dark:hover:bg-background-700 focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                        Failed
                    </button>
                    <button @click="openNotesModal(row.user.id, row.notes); $dispatch('open-modal', 'result-notes-modal')"
                        :disabled="!row.notes"
                        class="inline-flex items-center px-4 py-2 bg-info-500 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-background-50 hover:text-background-700 dark:hover:bg-background-700 focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                        Notes
                    </button>
                </td>
            </x-slot>
            
        @endif
        </x-table>


        <x-modal name="result-notes-modal" :show="$errors->userId->isNotEmpty()" focusable x-model="notesModal">
            <div class="p-6">
                <h2  class="text-lg font-medium text-background-900 dark:text-background-100" x-text="'Result notes for user ' + notesModal.resultid"></h2>
                <span x-text="notesModal.notes"></span>
            </div>
        </x-modal>

    </div>
</div>