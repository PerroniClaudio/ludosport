<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12 flex flex-col gap-8">
            <section class="bg-background-800 flex p-8 rounded">
                <div class="rounded-full h-24 w-24">
                    <img src="{{ route('profile-picture', $user->id) }}" alt="avatar" class="rounded-full h-24 w-24" />
                </div>
                <div class="flex-1 flex flex-col gap-2 ml-8">
                    <div class="w-1/2 flex flex-col gap-2">
                        <div class="text-4xl text-primary-500">{{ $user->name }} {{ $user->surname }}</div>
                        <div class="flex items-center gap-2">
                            <x-lucide-sword class="h-5 w-5 text-background-500 dark:text-background-400" />
                            <span class="text-sm text-background-500 dark:text-background-400">
                                {{ $user->battle_name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-lucide-flag class="h-5 w-5 text-background-500 dark:text-background-400" />
                            <span class="text-sm text-background-500 dark:text-background-400">
                                {{ $user->nation->name }}
                            </span>
                        </div>

                        <div>
                            <div
                                class="border border-background-700 text-background-800 dark:text-background-200 rounded-lg p-4 cursor-pointer flex items-center gap-2">
                                @switch($user->rank->id)
                                    @case(1)
                                        <x-lucide-user class="w-6 h-6 text-background-800 dark:text-background-200" />
                                        <p>{{ __('users.' . strtolower($user->rank->name)) }}</p>
                                    @break

                                    @case(2)
                                        <x-lucide-user-check class="w-6 h-6 text-background-800 dark:text-background-200" />
                                        <p>{{ __('users.' . strtolower($user->rank->name)) }}</p>
                                    @break

                                    @case(3)
                                        <x-lucide-graduation-cap class="w-6 h-6 text-background-800 dark:text-background-200" />
                                        <p>{{ __('users.' . strtolower($user->rank->name)) }}</p>
                                    @break

                                    @case(4)
                                        <x-lucide-shield-check class="w-6 h-6 text-background-800 dark:text-background-200" />
                                        <p>{{ __('users.' . strtolower($user->rank->name)) }}</p>
                                    @break

                                    @default
                                @endswitch
                            </div>
                        </div>


                        <div class="grid grid-cols-2 gap-2 text-background-800 dark:text-background-200"
                            x-data="{
                                selected: {{ collect($user->roles) }},
                            }">

                            @foreach ($roles as $role)
                                <div x-on:click="selectRole('{{ $role->label }}')"
                                    class="border border-background-700 rounded-lg p-4 cursor-pointer flex items-center gap-2"
                                    x-show="selected.includes('{{ $role->label }}')">

                                    @switch($role->label)
                                        @case('admin')
                                            <x-lucide-crown class="w-6 h-6 text-primary-500" />
                                        @break

                                        @case('athlete')
                                            <x-lucide-swords class="w-6 h-6 text-primary-500" />
                                        @break

                                        @case('rector')
                                            <x-lucide-graduation-cap class="w-6 h-6 text-primary-500" />
                                        @break

                                        @case('dean')
                                            <x-lucide-book-marked class="w-6 h-6 text-primary-500" />
                                        @break

                                        @case('manager')
                                            <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                        @break

                                        @case('technician')
                                            <x-lucide-wrench class="w-6 h-6 text-primary-500" />
                                        @break

                                        @case('instructor')
                                            <x-lucide-megaphone class="w-6 h-6 text-primary-500" />
                                        @break

                                        @default
                                    @endswitch

                                    <span>{{ __("users.{$role->label}") }}</span>
                                </div>
                            @endforeach

                            <input type="hidden" name="roles" x-model="selected">
                        </div>

                    </div>
                </div>

            </section>


            <div class="grid grid-cols-2 gap-4">

                <div
                    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 my-4 text-background-800 dark:text-background-200 ">
                    <h3 class="text-2xl">
                        {{ __('users.academies') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <h5 class="text-lg">{{ __('users.as_personnel') }}</h5>

                    <div class="flex flex-col gap-2">

                        @foreach ($user->academies as $academy)
                            <a href="{{ route('academy-profile', $academy->slug) }}"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $academy->name }}</span>
                            </a>
                        @endforeach

                    </div>

                    <h5 class="text-lg">{{ __('users.as_athlete') }}</h5>

                    <div class="flex flex-col gap-2">
                        @foreach ($user->academyAthletes as $academy)
                            <a href="{{ route('academy-profile', $academy->slug) }}"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $academy->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 my-4 text-background-800 dark:text-background-200">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('users.schools') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <h5 class="text-lg">{{ __('users.as_personnel') }}</h5>

                    <div class="flex flex-col gap-2">

                        @foreach ($user->schools as $school)
                            <a href="#"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $school->name }}</span>
                            </a>
                        @endforeach

                    </div>

                    <h5 class="text-lg">{{ __('users.as_athlete') }}</h5>

                    <div class="flex flex-col gap-2">
                        @foreach ($user->schoolAthletes as $schools)
                            <a href="#"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $schools->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            <div
                class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 my-4 text-background-800 dark:text-background-200 ">
                <h3 class="text-2xl">
                    {{ __('website.user_events_placement') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <x-table striped="false" :columns="[
                    [
                        'name' => 'Placement',
                        'field' => 'placement',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'Event Name',
                        'field' => 'event',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'War Points',
                        'field' => 'war_points',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'Style Points',
                        'field' => 'style_points',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                    [
                        'name' => 'Date',
                        'field' => 'date',
                        'columnClasses' => '', // classes to style table th
                        'rowClasses' => '', // classes to style table td
                    ],
                ]" :rows="$user->events" />
            </div>



        </section>
    </div>
</x-website-layout>
