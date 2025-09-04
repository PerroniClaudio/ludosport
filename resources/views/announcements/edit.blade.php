<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('announcements.edit_title') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col gap-4">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h3 class="text-background-800 dark:text-background-200 text-2xl">
                    {{ __('announcements.informations') }}
                </h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                <form method="POST" action="{{ route('announcements.update', $announcement->id) }}"
                    class="flex flex-col gap-4">
                    @csrf

                    <x-form.input name="object" label="Object" type="text" required="{{ true }}"
                        :value="$announcement->object" placeholder="{{ fake()->sentence() }}" />


                    <div class="flex-1">
                        <x-form.select name="type" label="Type" required="{{ true }}" :options="$types"
                            value="{{ $announcement->type }}" shouldHaveEmptyOption="true" />
                    </div>


                    <div class="flex items-top gap-4">
                        <div class="flex-1">
                            <x-announcements.roles-select :roles="$roles"
                                selected="{{ $announcement->roles != '' ? $announcement->roles : '[]' }}" />

                        </div>
                        <div class="flex-1">
                            <x-announcements.nation-select :nations="collect($nations)"
                                selected="{{ $announcement->nations != '' ? $announcement->nations : '[]' }}" />
                        </div>
                        <div class="flex-1">
                            <x-announcements.academies-select :academies="collect($academies)"
                                selected="{{ $announcement->academies != '' ? $announcement->academies : '[]' }}" />
                        </div>
                    </div>

                    <x-rich-text-editor name="content" label="Content" required="{{ true }}" :value="$announcement->content"
                        placeholder="Write a message..." />

                    <div class="flex items-end justify-end gap-4">
                        <x-primary-button>
                            {{ __('announcements.save') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <h3 class="text-background-800 dark:text-background-200 text-lg">
                    {{ __('announcements.users_have_seen') }}
                </h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                <x-table striped="false" :columns="[
                    [
                        'name' => 'Id',
                        'field' => 'id',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'Name',
                        'field' => 'name',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'Roles',
                        'field' => 'role',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'Seen at',
                        'field' => 'seen_at',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                ]" :rows="$haveseen">
                    <x-slot name="tableRows">
                        <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.id"></td>
                        <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.name"></td>
                        <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="row.role"></td>



                        <td class="text-background-500 dark:text-background-300 px-6 py-3 border-t border-background-100 dark:border-background-700 whitespace-nowrap"
                            x-text="new Date(row.seen_at).toLocaleDateString('it-IT', {
                                                    hour: 'numeric', 
                                                    minute: 'numeric' 
                                                })">
                        </td>

                    </x-slot>

                </x-table>

            </div>

            <x-announcements.disable-announcement :announcement="$announcement" />
        </div>
    </div>

</x-app-layout>
