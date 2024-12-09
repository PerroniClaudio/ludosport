<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12 flex flex-col gap-8">
            <section class="bg-white dark:bg-background-800 flex p-8 rounded">
                <div class="rounded-full h-24 w-24">
                    <img src="{{ route('academy-image', $academy->id) }}" alt="avatar" class="rounded-full h-24 w-24" />
                </div>
                <div class="flex-1 flex flex-col gap-2 ml-8">
                    <div class="w-1/2 flex flex-col gap-2">
                        <div class="text-4xl text-primary-500">{{ $academy->name }}</div>
                        <div class="flex items-center gap-2">
                            <x-lucide-flag class="h-5 w-5 text-background-500 dark:text-background-400" />
                            <span class="text-sm text-background-500 dark:text-background-400">
                                {{ $academy->nation->name }}
                            </span>
                            <img src="{{ route('nation-flag', $academy->nation->id) }}"
                                alt="{{ $academy->nation->flag }}" class="h-2 w-4">
                        </div>
                        <div class="flex items-center gap-2">
                            <x-lucide-circle-user-round class="h-5 w-5 text-background-500 dark:text-background-400" />
                            <span class="text-sm text-background-500 dark:text-background-400">
                                {{ __('users.rector') }}: {{ $rector }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section
                class="bg-white dark:bg-background-800 text-background-800 dark:text-background-200 flex flex-col p-8 rounded order-2 lg:order-1">
                <h4 class="text-2xl">{{ __('website.academies_detail_users') }}</h4>

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
                        'name' => 'Battle Name',
                        'field' => 'battle_name',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                ]" :rows="$academy->athletes">

                </x-table>


            </section>

            <section
                class="bg-white dark:bg-background-800 text-background-800 dark:text-background-200 flex flex-col p-8 rounded order-2 lg:order-1">
                <h4 class="text-2xl">{{ __('website.academies_detail_personnel') }}</h4>

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
                        'name' => 'Battle Name',
                        'field' => 'battle_name',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'Role',
                        'field' => 'role',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                ]" :rows="$personnel">

                </x-table>


            </section>

        </section>
    </div>
</x-website-layout>
