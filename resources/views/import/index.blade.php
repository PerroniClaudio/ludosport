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
                <div class="p-6 text-background-900 dark:text-background-100">
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
                            'field' => 'created_at',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$imports">
                        <x-slot name="tableRows">
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.id"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.type"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.status"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="row.user.name + ' ' + row.user.surname"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                                x-text="new Date(row.created_at).toLocaleDateString('it-IT', {
                                    hour: 'numeric', 
                                    minute: 'numeric' 
                                })">
                            </td>
                        </x-slot>

                    </x-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
