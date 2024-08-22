<x-app-layout>
    {{-- @php
        $authRole = auth()->user()->getRole();
        $createRoute = $authRole === 'admin' ? 'users.create' : $authRole . '.users.create';
        $editRoute = $authRole === 'admin' ? 'users.edit' : $authRole . '.users.edit';
    @endphp --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('users.search_result') }}
            </h2>
            {{-- <div>
                <x-create-new-button :href="route($createRoute)" />
            </div> --}}
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
                                        <span class="text-sm">
                                            @foreach ($user->roles as $index => $role)
                                                {{ __('users.' . $role->label) . ($index < (count($user->roles) - 1) ? ', ' : '') }}
                                            @endforeach
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-lucide-graduation-cap
                                        class="h-5 w-5 text-background-500 dark:text-background-400" />
                                    <div>
                                        @php
                                            $mergedAcademies = $user->academies->merge($user->academyAthletes);
                                        @endphp
                                        <span class="text-sm">
                                            @if (count($mergedAcademies) > 0)
                                                
                                                @foreach ($mergedAcademies as $index => $academy)
                                                    {{ $academy->name . ($index < (count($mergedAcademies) - 1) ? ', ' : '') }}
                                                @endforeach
                                            @else
                                                {{ __('users.no_academies') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-lucide-swords class="h-5 w-5 text-background-500 dark:text-background-400" />
                                    <div>
                                        @php
                                            $mergedSchools = $user->schools->merge($user->schoolAthletes);
                                        @endphp
                                        <span class="text-sm">
                                            @if(count($mergedSchools) > 0)
                                                @foreach ($mergedSchools as $index => $school)
                                                    {{ $school->name . ($index < (count($mergedSchools) - 1) ? ', ' : '') }}
                                                @endforeach
                                            @else
                                                {{ __('users.no_schools') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <x-lucide-flag class="h-5 w-5 text-background-500 dark:text-background-400" />
                                    <div>
                                        <span class="text-sm">{{ $user->nation->name }}</span>
                                    </div>
                                </div>

                            </div>

                            {{-- <a href="{{ route($editRoute, $user->id) }}">
                                <x-secondary-button>
                                    <x-lucide-edit class="h-5 w-5 text-white" />
                                </x-secondary-button>
                            </a> --}}

                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

</x-app-layout>