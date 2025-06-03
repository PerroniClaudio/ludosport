<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 sm:px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12 flex flex-col gap-8">
            <section class="bg-white dark:bg-background-800 flex p-4 lg:p-8 sm:rounded-lg">
                <div class="rounded-full h-24 w-24 hidden lg:block shrink-0">
                    <img src="{{ route('profile-picture', $user->id) }}" alt="avatar"
                        class="rounded-full h-24 w-24 object-cover object-center" />
                </div>
                <div class="flex-1 flex flex-col gap-2 lg:ml-8">
                    <div class="lg:w-1/2 flex flex-col gap-2">
                        <div class="text-primary-500 flex items-center gap-2">
                            <div class="rounded-full h-12 w-12 lg:hidden block shrink-0">
                                <img src="{{ route('profile-picture', $user->id) }}" alt="avatar"
                                    class="rounded-full h-12 w-12 object-cover object-center" />
                            </div>

                            <span class="text-xl sm:text-3xl lg:text-4xl">{{ $user->name }}
                                {{ $user->surname }}</span>

                            @if ($user->has_paid_fee)
                                <x-lucide-verified class="h-6 w-6 text-primary-500" />
                            @endif
                        </div>
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
                            <img src="{{ route('nation-flag', $user->nation->id) }}" alt="{{ $user->nation->name }}"
                                class="h-2 w-4">
                        </div>

                        <div>
                            <div
                                class="border border-background-700 text-background-800 dark:text-background-200 rounded-full p-2 cursor-pointer flex items-center gap-2">
                                <img src="{{ route('rank-image', $user->rank->id) }}" alt="rank"
                                    class="rounded-full h-8 w-8 shrink-0" />
                                <p class="text-sm sm:text-base">{{ __('users.' . strtolower($user->rank->name)) }}</p>
                            </div>
                        </div>

                        @if ($user->instagram != '')
                            <div>
                                <a href="https://www.instagram.com/{{ $user->instagram }}" target="_blank"
                                    class="border border-background-700 text-background-800 dark:text-background-200 rounded-full p-4 cursor-pointer flex items-center gap-2">
                                    <x-lucide-camera
                                        class="w-6 h-6 text-background-800 dark:text-background-200 shrink-0" />
                                    <p class="break-all text-sm sm:text-base">{{ $user->instagram }}</p>
                                </a>
                            </div>
                        @endif


                        <div class="grid max-[500px]:grid-cols-1 grid-cols-2 gap-2 text-background-800 dark:text-background-200"
                            x-data="{
                                selected: {{ collect($user->roles) }},
                            }">

                            @foreach ($roles as $role)
                                <div x-on:click="selectRole('{{ $role->label }}')"
                                    class="border border-background-700 rounded-full p-4 cursor-pointer flex items-center gap-2"
                                    x-show="selected.includes('{{ $role->label }}')">

                                    @switch($role->label)
                                        @case('admin')
                                            <x-lucide-crown class="w-6 h-6 text-primary-500 shrink-0" />
                                        @break

                                        @case('athlete')
                                            <x-lucide-swords class="w-6 h-6 text-primary-500 shrink-0" />
                                        @break

                                        @case('rector')
                                            <x-lucide-graduation-cap class="w-6 h-6 text-primary-500 shrink-0" />
                                        @break

                                        @case('dean')
                                            <x-lucide-book-marked class="w-6 h-6 text-primary-500 shrink-0" />
                                        @break

                                        @case('manager')
                                            <x-lucide-briefcase class="w-6 h-6 text-primary-500 shrink-0" />
                                        @break

                                        @case('technician')
                                            <x-lucide-wrench class="w-6 h-6 text-primary-500 shrink-0" />
                                        @break

                                        @case('instructor')
                                            <x-lucide-megaphone class="w-6 h-6 text-primary-500 shrink-0" />
                                        @break

                                        @default
                                    @endswitch

                                    <span class="text-sm sm:text-base">{{ __("users.{$role->label}") }}</span>
                                </div>
                            @endforeach


                        </div>

                    </div>
                </div>

            </section>

            <div class="lg:w-1/2">
                <x-user.weapon-forms-show :forms="$user->weaponForms" :user="$user" />
            </div>

            @if ($user->bio != '')
                <section
                    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg text-sm sm:text-base p-4 sm:p-8 text-background-800 dark:text-background-200">
                    <div class="flex-1">
                        <h3 class="text-2xl">{{ __('website.user_profile_bio') }}</h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <p class="text-background-800 dark:text-background-200">{{ $user->bio }}</p>
                    </div>
                </section>
            @endif


            <div class="grid lg:grid-cols-2 gap-4">

                <div
                    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 my-4 text-background-800 dark:text-background-200 ">
                    <h3 class="text-2xl">
                        {{ __('users.academies') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <h5 class="text-lg">{{ __('users.as_personnel') }}</h5>

                    <div class="flex flex-col gap-2">

                        @foreach ($user->academies as $academy)
                            <a href="#"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $academy->name }}</span>
                            </a>
                        @endforeach

                    </div>

                    <h5 class="text-lg">{{ __('users.as_athlete') }}</h5>

                    <div class="flex flex-col gap-2">
                        @foreach ($user->academyAthletes as $academy)
                            <a href="#"
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
                            <a href="{{ route('school-profile', $school->slug) }}"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $school->name }}</span>
                            </a>
                        @endforeach

                    </div>

                    <h5 class="text-lg">{{ __('users.as_athlete') }}</h5>

                    <div class="flex flex-col gap-2">
                        @foreach ($user->schoolAthletes as $school)
                            <a href="{{ route('school-profile', $school->slug) }}"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $school->name }}</span>
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
                        'name' => 'Arena Points',
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
