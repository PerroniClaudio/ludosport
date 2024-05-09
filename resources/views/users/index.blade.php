<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('users.title') }}
            </h2>
            <div>
                <x-create-new-button :href="route('users.create')" />
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <x-table 
                        striped="false" 
                        :columns="[
                            [
                                'name' => 'Name',
                                'field' => 'name',
                                'columnClasses' => '', // classes to style table th
                                'rowClasses' => '', // classes to style table td
                            ],
                            [
                                'name' => 'Surname',
                                'field' => 'surname',
                                'columnClasses' => '', // classes to style table th
                                'rowClasses' => '', // classes to style table td
                            ],
                            [
                                'name' => 'Email',
                                'field' => 'email',
                                'columnClasses' => '',
                                'rowClasses' => '',
                            ],
                            [
                                'name' => 'Fee',
                                'field' => 'has_paid_fee',
                                'columnClasses' => '',
                                'rowClasses' => '',
                            ],
                        ]"
                        :rows="$users"
                    >   
                        <x-slot name="tableRows">
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.name"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.surname"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap" x-text="row.email"></td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                <x-lucide-badge-check class="w-5 h-5 text-primary-800 dark:text-primary-500" x-show="row.has_paid_fee == 1" />
                                <x-lucide-badge-info class="w-5 h-5 text-red-800 dark:text-red-500" x-show="row.has_paid_fee == 0" />
                            </td>
                            <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap">
                                <a x-bind:href="'/users/' + row.id" >
                                    <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                </a>
                            </td>
                        </x-slot>
                    
                        <x-slot name="tableActions">

                        </x-slot>

                    </x-table>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>