<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('nations.title') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4">
                @foreach($continents as $key => $nations)
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ $key }}</h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <x-table 
                                striped="false"
                                :columns="[
                                    [
                                        'name' => 'Name',
                                        'field' => 'name',
                                        'columnClasses' => '',
                                        'rowClasses' => '', 
                                    ],
                                    [
                                        'name' => 'Code',
                                        'field' => 'code',
                                        'columnClasses' => '',
                                        'rowClasses' => '', 
                                    ],
                                ]"
                                :rows="$nations"
                            >
                                <x-slot name="tableActions">
                                    <a x-bind:href="'/nations/' + row.id" >
                                        <x-lucide-pencil class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                    </a>
                                </x-slot>
                            </x-table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</x-app-layout>