<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('ranks.requests_title') }}
            </h2>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100" x-data="{
                    reason: '',
                    request_id: 0,
                    csrf: '{{ csrf_token() }}',
                    showReadReasonModal: function(reason) {
                        this.reason = reason
                        $dispatch('open-modal', 'read-reason-modal')
                    },
                    showAcceptRequestModal: function(id) {
                        this.request_id = id
                        $dispatch('open-modal', 'accept-request-modal')
                    },
                    showAcceptAllRequestModal: function(id) {
                        this.request_id = 0
                        $dispatch('open-modal', 'accept-all-request-modal')
                    },
                    showRejectRequestModal: function(id) {
                        this.request_id = id
                        $dispatch('open-modal', 'Reject-request-modal')
                    },
                    confirmAcceptRequest: async function() {
                        window.location.href = '/rank-requests/' + this.request_id + '/approve'
                
                    },
                    confirmAcceptAllRequest: async function() {
                        window.location.href = '/rank-requests/approve-all'
                
                    },
                    confirmRejectRequest: async function() {
                        window.location.href = '/rank-requests/' + this.request_id + '/reject'
                
                    }
                }">
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Id',
                            'field' => 'id',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'User',
                            'field' => 'user_to_promote',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Rank',
                            'field' => 'rank',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Requested By',
                            'field' => 'requested_by',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Date',
                            'field' => 'created_at',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Actions',
                            'field' => 'id',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$requests">
                        <x-slot name="tableRows">
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.id"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.user_to_promote"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.rank"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.requested_by"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.created_at"></td>
                            <td
                                class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="has-tooltip">
                                        <div class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                                            {{ __('ranks.requests_read_reason') }}
                                        </div>
                                        <a @click="showReadReasonModal(row.reason)">
                                            <x-lucide-mail
                                                class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                        </a>
                                    </div>
                                    <div class="has-tooltip">
                                        <div class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                                            {{ __('ranks.requests_accept') }}
                                        </div>
                                        <a @click="showAcceptRequestModal(row.id)">
                                            <x-lucide-check
                                            class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                        </a>
                                    </div>
                                    <div class="has-tooltip">
                                        <div class="tooltip rounded shadow-lg p-1 bg-background-100 text-background-800 text-sm max-w-[800px] -mt-6 -translate-y-full">
                                            {{ __('ranks.requests_reject') }}
                                        </div>
                                        <a @click="showRejectRequestModal(row.id)">
                                            <x-lucide-circle-x
                                            class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </x-slot>

                    </x-table>

                    <div class="flex flex-row-reverse w-full">
                        <x-primary-button @click="showAcceptAllRequestModal" type="button">
                            {{ __('ranks.requests_accept_all') }}
                        </x-primary-button>
                    </div>


                    <x-modal name="read-reason-modal" :show="false">
                        <div class="p-6 flex flex-col gap-4">
                            <p x-text="reason"></p>
                        </div>
                    </x-modal>
                    <x-modal name="accept-request-modal" :show="false">
                        <div class="p-6 flex flex-col gap-4">
                            <p>{{ __('ranks.requests_accept_text') }}</p>
                            <div class="flex justify-end gap-4">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'accept-request-modal')">
                                    {{ __('ranks.requests_cancel') }}
                                </x-secondary-button>
                                <x-primary-button @click="confirmAcceptRequest">
                                    {{ __('ranks.requests_accept') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </x-modal>
                    <x-modal name="accept-all-request-modal" :show="false">
                        <div class="p-6 flex flex-col gap-4">
                            <p>{{ __('ranks.requests_accept_text_all') }}</p>
                            <div class="flex justify-end gap-4">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'accept-all-request-modal')">
                                    {{ __('ranks.requests_cancel') }}
                                </x-secondary-button>
                                <x-primary-button @click="confirmAcceptAllRequest">
                                    {{ __('ranks.requests_accept') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </x-modal>
                    <x-modal name="Reject-request-modal" :show="false">
                        <div class="p-6 flex flex-col gap-4">
                            <p>{{ __('ranks.requests_reject_text') }}</p>
                            <div class="flex justify-end gap-4">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'Reject-request-modal')">
                                    {{ __('ranks.requests_cancel') }}
                                </x-secondary-button>
                                <x-primary-button @click="confirmRejectRequest">
                                    {{ __('ranks.requests_reject') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </x-modal>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
