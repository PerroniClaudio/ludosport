<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('imports.title') }}
            </h2>
            <div>
                @php
                    $authRole = Auth::user()->getRole();
                    $redirectRoute = $authRole === 'admin' ? 'imports.create' : $authRole . '.imports.create';
                @endphp
                <x-create-new-button :href="route($redirectRoute)" />
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100" x-data="{
                    logsModal: {
                        importid: null,
                        logs: null,
                        showModal: false,
                    },
                    openLogsModal: function(importid, logs) {
                        this.logsModal.importid = importid;
                        this.logsModal.logs = logs ? JSON.parse(logs) : 'No logs';
                        this.logsModal.showModal = true;
                        console.log(logs)
                    },
                }">
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Id',
                            'field' => 'id',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Type',
                            'field' => 'type',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Status',
                            'field' => 'status',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Author',
                            'field' => 'author',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Created at',
                            'field' => 'created_at_formatted',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$imports">

                        <x-slot name="tableActions">
                            <template x-if="(row.user_id == {{ Auth::id() }}) || ('{{ $authRole }}' == 'admin')">
                                <x-primary-button
                                    @click="openLogsModal(row.id, row.log); $dispatch('open-modal', 'import-log-modal')">
                                    {{-- <span>{{ __('imports.log') }}</span> --}}
                                    <x-lucide-logs class="w-5 h-5 cursor-pointer" />
                                </x-primary-button>
                            </template>
                            <x-primary-button>
                                <a x-bind:href="'/imports/download/' + row.id">
                                    <x-lucide-download class="w-5 h-5 cursor-pointer" />
                                </a>
                            </x-primary-button>
                            <x-primary-button x-show="row.event_id != null">
                                <a x-bind:href="'/events/' + row.event_id">
                                    <x-lucide-eye class="w-5 h-5 cursor-pointer" />
                                </a>
                            </x-primary-button>
                        </x-slot>
                    </x-table>
                    <x-modal name="import-log-modal" :show="$errors->userId->isNotEmpty()" focusable x-model="logsModal">
                        <div class="p-6">
                            <h2 class="text-lg font-medium text-background-900 dark:text-background-100"
                                x-text="'Logs for import #' + logsModal.importid"></h2>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <ul>
                                <template x-if="logsModal.logs != null">
                                    <template x-for="log in logsModal.logs">
                                        <li x-text="log"></li>
                                    </template>
                                </template>
                            </ul>
                        </div>
                    </x-modal>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
