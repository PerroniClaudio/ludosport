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
                <div class="p-6 text-background-900 dark:text-background-100">
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
                    ]" :rows="$requests">
                        <x-slot name="tableActions">
                            <a x-bind:href="#">
                                <x-lucide-mail class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                            <a x-bind:href="#">
                                <x-lucide-check class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                            <a x-bind:href="#">
                                <x-lucide-circle-x
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>

                    <div class="flex flex-row-reverse w-full">
                        <x-primary-button>
                            {{ __('ranks.requests_accept_all') }}
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
