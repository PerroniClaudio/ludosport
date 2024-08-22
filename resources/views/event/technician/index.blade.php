<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('events.title') }}
            </h2>
            {{-- <div>
                <x-create-new-button :href="route('technician.events.create')" />
            </div> --}}
        </div>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-12 gap-4" x-data="{
                selectedType: 'pending',
            }">
                <div class="col-span-3">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('events.type') }}
                            </h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <div class="flex flex-col gap-2">

                                <button
                                    class="hover:text-background-800 dark:hover:text-background-300 focus:outline-none  text-left"
                                    :class="{ 'text-primary-600': selectedType == 'pending' }"
                                    @click="selectedType = 'pending'">
                                    {{ __('events.pending') }}
                                </button>

                                <button
                                    class="hover:text-background-800 dark:hover:text-background-300 focus:outline-none  text-left"
                                    :class="{ 'text-primary-600': selectedType == 'approved' }"
                                    @click="selectedType = 'approved'">
                                    {{ __('events.approved') }}
                                </button>

                            </div>
                        </div>
                    </div>
                </div>
                @php
                    foreach ($pending_events as $key => $value) {
                        $pending_events[$key]['start_date'] = date('d/m/Y H:i', strtotime($value['start_date']));
                        $pending_events[$key]['end_date'] = date('d/m/Y H:i', strtotime($value['end_date']));
                    }
                    foreach ($approved_events as $key => $value) {
                        $approved_events[$key]['start_date'] = date('d/m/Y H:i', strtotime($value['start_date']));
                        $approved_events[$key]['end_date'] = date('d/m/Y H:i', strtotime($value['end_date']));
                    }
                @endphp
                <div class="col-span-9">
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg"
                        x-show="selectedType == 'pending'" x-cloak>
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('events.pending') }}
                            </h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <x-table :columns="[
                                [
                                    'name' => 'Name',
                                    'field' => 'name',
                                    'columnClasses' => '', // classes to style table th
                                    'rowClasses' => '', // classes to style table td
                                ],
                                [
                                    'name' => 'Start Date',
                                    'field' => 'start_date',
                                    'columnClasses' => '', // classes to style table th
                                    'rowClasses' => '', // classes to style table td
                                ],
                                [
                                    'name' => 'End Date',
                                    'field' => 'end_date',
                                    'columnClasses' => '', // classes to style table th
                                    'rowClasses' => '', // classes to style table td
                                ],
                            ]" :rows="$pending_events">


                                <x-slot name="tableActions">
                                    <a x-bind:href="'/technician/events/' + row.id">
                                        <x-lucide-pencil
                                            class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                    </a>
                                </x-slot>


                            </x-table>

                        </div>
                    </div>
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg"
                        x-show="selectedType == 'approved'" x-cloak>
                        <div class="p-6 text-background-900 dark:text-background-100">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('events.approved') }}
                            </h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <x-table :columns="[
                                [
                                    'name' => 'Name',
                                    'field' => 'name',
                                    'columnClasses' => '', // classes to style table th
                                    'rowClasses' => '', // classes to style table td
                                ],
                                [
                                    'name' => 'Start Date',
                                    'field' => 'start_date',
                                    'columnClasses' => '', // classes to style table th
                                    'rowClasses' => '', // classes to style table td
                                ],
                                [
                                    'name' => 'End Date',
                                    'field' => 'end_date',
                                    'columnClasses' => '', // classes to style table th
                                    'rowClasses' => '', // classes to style table td
                                ],
                            ]" :rows="$approved_events" >

                                <x-slot name="tableActions">
                                    <a x-bind:href="'/technician/events/' + row.id">
                                        <x-lucide-pencil
                                            class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                                    </a>
                                </x-slot>
                                
                            </x-table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
