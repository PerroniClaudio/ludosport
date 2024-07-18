<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('fees.title') }}
            </h2>

        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.purchase_fees') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div
                                class="border border-background-700 text-background-800 dark:text-background-200 rounded-lg p-4 cursor-pointer flex flex-col gap-2">
                                <p>{{ __('fees.buy_new_fees') }}</p>
                                <div class="flex justify-end ">
                                    <a href="{{ route('dean.fees.purchase') }}">
                                        <x-primary-button>
                                            <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                        </x-primary-button>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div
                                class="border border-background-700 text-background-800 dark:text-background-200 rounded-lg p-4 cursor-pointer flex flex-col gap-2">
                                {{ __('fees.renew_expired_fees') }}
                                <div class="flex justify-end ">
                                    <a href="#">
                                        <x-primary-button>
                                            <x-lucide-arrow-right class="h-6 w-6 text-white" />
                                        </x-primary-button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('fees.fees_list') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Id',
                            'field' => 'id',
                        ],
                        [
                            'name' => 'Used',
                            'field' => '',
                        ],
                        [
                            'name' => 'Assigned to',
                            'field' => '',
                        ],
                        [
                            'name' => 'Start date',
                            'field' => '',
                        ],
                        [
                            'name' => 'End date',
                            'field' => '',
                        ],
                        [
                            'name' => 'Auto-renew',
                            'field' => '',
                        ],
                    ]" :rows="$fees">
                        <x-slot name="tableRows">
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.name"></td>

                        </x-slot>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
