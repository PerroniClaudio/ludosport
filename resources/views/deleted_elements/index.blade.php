<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('deleted.title') }}
            </h2>
            <div>
                <x-create-new-button :href="route('events.create')" />
            </div>
        </div>
    </x-slot>
    <div class="py-12" x-data="{
        selectedElementId: 0,
        selectedElementType: '',
        restore: function(id, type) {
            console.log(id, type);
            this.selectedElementId = id;
            this.selectedElementType = type;
            $dispatch('open-modal', 'restore-modal')
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('deleted.users') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
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
                    ]" :rows="$deleted_users">
                        <x-slot name="tableActions">
                            <a x-on:click="restore(row.id, 'user')">
                                <x-lucide-history
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            </div>
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('deleted.academies') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Name',
                            'field' => 'name',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$deleted_academies">
                        <x-slot name="tableActions">
                            <a x-on:click="restore(row.id, 'academy')">
                                <x-lucide-history
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            </div>
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('deleted.schools') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <x-table striped="false" :columns="[
                        [
                            'name' => 'Name',
                            'field' => 'name',
                            'columnClasses' => '', // classes to style table th
                            'rowClasses' => '', // classes to style table td
                        ],
                    ]" :rows="$deleted_schools">
                        <x-slot name="tableActions">
                            <a x-on:click="restore(row.id, 'school')">
                                <x-lucide-history
                                    class="w-5 h-5 text-primary-800 dark:text-primary-500 cursor-pointer" />
                            </a>
                        </x-slot>
                    </x-table>
                </div>
            </div>
        </div>

        <x-modal name="restore-modal" focusable>
            <form method="post" action="{{ route('deleted-elements.restore') }}" class="p-6" x-ref="form">
                @csrf
                <p class="mt-1 text-sm text-background-600 dark:text-background-400">
                    {{ __('deleted.restore_message') }}
                </p>
                <input type="hidden" name="element_id" x-model="selectedElementId">
                <input type="hidden" name="element_type" x-model="selectedElementType">
                <div class="flex justify-end gap-2 mt-4">
                    <x-secondary-button x-on:click.prevent="$dispatch('close-modal')">
                        <span>{{ __('deleted.cancel') }}</span>
                    </x-secondary-button>
                    <x-primary-button x-on:click.prevent="$refs.form.submit()">
                        <span>{{ __('deleted.restore') }}</span>
                    </x-primary-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
