<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('Edit Nation') }} - {{ $nation->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 mb-4">
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <div class="flex justify-between">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('navigation.accademie') }}</h3>
                        <x-nations.academies :nation="$nation" :academies="$academies" />
                    </div>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
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
                            ]
                        ]"
                        :rows="$nation->academies"
                    >
                        <x-slot name="tableActions">
                            <a x-bind:href="'/academies/' + row.id" >
                                <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>