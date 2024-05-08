<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">



                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Name',
                            'field' => 'name',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                        [
                            'name' => 'Email',
                            'field' => 'email',
                            'columnClasses' => '',
                            'rowClasses' => '',
                        ],
                    ]" :rows="$rows">
                         <x-slot name="tableActions">
                            <div class="flex flex-wrap space-x-4">
                                <button>ciao</button>
                            </div>
                        </x-slot>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
