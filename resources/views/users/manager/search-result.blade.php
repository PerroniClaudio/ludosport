<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('users.search_result') }}
            </h2>
            <div>
                <x-create-new-button :href="route('manager.users.create')" />
            </div>
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-user.search />

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="flex flex-col gap-2 p-6 text-background-900 dark:text-background-100">
                    @foreach ($users as $user)
                        <div class="flex items-center gap-2 p-2 rounded-md bg-background-50 dark:bg-background-900">
                            <div class="flex-1 flex flex-col gap-2">
                                <div class="text-xl">{{ $user->name }} {{ $user->surname }}</div>
                                <div class="text-sm text-background-500 dark:text-background-400">
                                    {{ $user->email }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-lucide-user class="h-5 w-5 text-background-500 dark:text-background-400" />
                                    <div>
                                        @foreach ($user->roles as $role)
                                            <span class="text-sm">{{ __('users.' . $role->label) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-lucide-graduation-cap
                                        class="h-5 w-5 text-background-500 dark:text-background-400" />
                                    <div>
                                        @if (count($user->academies) > 0)
                                            @foreach ($user->academies as $academy)
                                                <span class="text-sm">{{ $academy->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-sm">{{ __('users.no_academies') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-lucide-swords class="h-5 w-5 text-background-500 dark:text-background-400" />
                                    <div>
                                        @foreach ($user->academyAthletes as $academy)
                                            <span class="text-sm">{{ $academy->name }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-lucide-flag class="h-5 w-5 text-background-500 dark:text-background-400" />
                                    <div>
                                        <span class="text-sm">{{ $user->nation->name }}</span>
                                    </div>
                                </div>

                            </div>

                            <a href="{{ route('manager.users.edit', $user->id) }}">
                                <x-secondary-button>
                                    <x-lucide-edit class="h-5 w-5 text-white" />
                                </x-secondary-button>
                            </a>

                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-app-layout>
