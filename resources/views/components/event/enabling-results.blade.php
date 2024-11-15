@props([
    'event' => '',
    'results' => [],
])
@php
    $authRole = auth()->user()->getRole();
@endphp

<div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-background-900 dark:text-background-100" 
    x-data="enablingresults({{ $event->id }})">
    
    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('events.enabling_results') }}</h3>

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
        <x-slot name="tableRows">
            <template x-for="(column, columnIndex) in columns" :key="'column-' + columnIndex">
                <template x-if="column.field !== 'actions'">
                    <td :class="`${column.rowClasses}`"
                        class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                        
                        <div x-show="column.field != 'result' && (column.field != 'stage')" x-text="`${row[column.field]}`"></div>
                        
                        <div x-show="column.field == 'result'" :class="`${column.field == 'result' ? (row[column.field] == 'passed' ? 'bg-success-500' : (row[column.field] == 'review' ? 'bg-warning-500' : (row[column.field] == 'failed' ? 'bg-error-500' : 'bg-background-500'))) + ' px-1 rounded text-white' : ''}`">
                            <div x-show="row[column.field] == 'passed'" x-text="'{{ __('events.result_passed') }}'"></div>
                            <div x-show="row[column.field] == 'failed'" x-text="'{{ __('events.result_failed') }}' + (row['retake'] ? (row['retake'] == 'course' ? ' ({{ __('events.result_retake_course_short')}})' : (row['retake'] == 'exam' ? ' ({{ __('events.result_retake_exam_short')}})' : '')) : '')"></div>
                            <div x-show="row[column.field] == 'review'" x-text="'{{ __('events.result_review') }}'"></div>
                            <div x-show="row[column.field] == 'pending'" x-text="'{{ __('events.result_pending') }}'"></div>
                            <div x-show="row[column.field] == null" x-text="'{{ __('events.result_null') }}'"></div>
                        </div>
                        
                        <div x-show="column.field == 'stage'" >
                            <div x-show="row[column.field] == 'registered'" x-text="'{{ __('events.result_stage_registered') }}'"></div>
                            <div x-show="row[column.field] == 'pending'" x-text="'{{ __('events.result_stage_pending') }}'"></div>
                            <div x-show="row[column.field] == 'confirmed'" x-text="'{{ __('events.result_stage_confirmed') }}'"></div>
                        </div>
                        
                    </td>
                </template>
            </template>
            @if ($authRole === 'admin')
                <td
                    class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                    <button @click="submitEnablingResult(row.id, 'passed')" 
                        :disabled="row.stage === 'confirmed'"
                        class="inline-flex items-center px-4 py-2 bg-success-500 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-background-50 hover:text-background-700 dark:hover:bg-background-700 focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                        Green
                    </button>
                    <button @click="submitEnablingResult(row.id, 'failed', 'exam')"
                        :disabled="row.stage === 'confirmed'"
                        class="inline-flex items-center px-4 py-2 bg-error-500 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-background-50 hover:text-background-700 dark:hover:bg-background-700 focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                        Exam
                    </button>
                    <button @click="submitEnablingResult(row.id, 'failed', 'course')"
                        :disabled="row.stage === 'confirmed'"
                        class="inline-flex items-center px-4 py-2 bg-error-500 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-background-50 hover:text-background-700 dark:hover:bg-background-700 focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                        Course
                    </button>
                    <button @click="openNotesModal(row.user.id, row.notes, row.internship_duration, row.internship_notes, row.retake); $dispatch('open-modal', 'result-notes-modal')"
                        :disabled="!(row.notes || row.internship_duration || row.internship_notes || row.retake)"
                        class="inline-flex items-center px-4 py-2 bg-info-500 rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-background-50 hover:text-background-700 dark:hover:bg-background-700 focus:outline-none disabled:opacity-25 transition ease-in-out duration-150">
                        Notes
                    </button>
                </td>
            @endif
        </x-slot>
        
    </x-table>


    <x-modal name="result-notes-modal" :show="$errors->userId->isNotEmpty()" focusable x-model="notesModal">
        <div class="p-6">
            <h2  class="text-lg font-medium text-background-900 dark:text-background-100" x-text="'Result notes for user ' + notesModal.resultid"></h2>
            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
            <template x-if="notesModal.internshipDuration">
                <div >
                    <h3 class="font-semibold">Internship duration</h3>
                    <span x-text="notesModal.internshipDuration"></span>
                </div>
            </template>
            <template x-if="notesModal.internshipNotes">
                <div >
                    <h3 class="font-semibold">Internship notes</h3>
                    <span x-text="notesModal.internshipNotes"></span>
                </div>
            </template>
            <template x-if="notesModal.retake">
                <div >
                    <h3 class="font-semibold">Retake</h3>
                    <span x-text="notesModal.retake"></span>
                </div>
            </template>
            <template x-if="notesModal.notes">
                <div >
                    <h3 class="font-semibold">Notes</h3>
                    <span x-text="notesModal.notes"></span>
                </div>
            </template>
        </div>
    </x-modal>

</div>