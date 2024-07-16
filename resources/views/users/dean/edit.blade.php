{{-- 
    Dean cannot assign roles higher than his own.
--}}
@php
    $no_access_roles = ['admin', 'rector'];
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('Edit User') }}
            </h2>
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 mb-4">
                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.status') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="w-1/2 flex flex-col gap-2">
                        <div>
                            @if ($user->hasRole('athlete'))
                                @if ($user->has_paid_fee)
                                    <div
                                        class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                        <x-lucide-check-circle class="w-6 h-6 text-green-500" />
                                        <span>{{ __('users.fee_paid') }}</span>
                                    </div>
                                @else
                                    <div
                                        class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                        <x-lucide-x-circle class="w-6 h-6 text-red-500" />
                                        <span>{{ __('users.fee_not_paid') }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div>
                            @if (!$user->is_disabled)
                                <div
                                    class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                    <x-lucide-check-circle class="w-6 h-6 text-green-500" />
                                    <span>{{ __('users.active') }}</span>
                                </div>
                            @else
                                <div
                                    class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                    <x-lucide-x-circle class="w-6 h-6 text-red-500" />
                                    <span>{{ __('users.disabled') }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            @if ($user->is_verified)
                                <div
                                    class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                    <x-lucide-check-circle class="w-6 h-6 text-green-500" />
                                    <span>{{ __('users.verified') }}</span>
                                </div>
                            @else
                                <div
                                    class="mt-1 text-sm text-background-600 dark:text-background-200 flex flex-row items-center gap-2">
                                    <x-lucide-x-circle class="w-6 h-6 text-red-500" />
                                    <span>{{ __('users.not_verified') }}</span>
                                    <form method="POST" action="{{ route('verification.send-for-user', $user->id) }}">
                                        @csrf
                                        <x-primary-button>
                                            <span>{{ __('users.send_verification_email') }}</span>
                                        </x-primary-button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('dean.users.update', $user->id) }}" class="grid grid-cols-2 gap-4">
                @csrf

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('users.personal_details_message') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="flex flex-col gap-2">
                        <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                            value="{{ $user->name }}" placeholder="{{ fake()->firstName() }}" />
                        <x-form.input name="surname" label="Surname" type="text" required="{{ true }}"
                            value="{{ $user->surname }}" placeholder="{{ fake()->lastName() }}" />
                        <x-form.input name="email" label="Email" type="email" required="{{ true }}"
                            value="{{ $user->email }}" placeholder="{{ fake()->email() }}" />
                        <x-form.input name="year" label="Subscription year" type="text"
                            required="{{ true }}" value="{{ $user->subscription_year }}"
                            placeholder="{{ date('Y') }}" />

                        <div>
                            <x-input-label for="nationality" value="Nationality" />
                            <select name="nationality" id="nationality"
                                class="w-full border-background-300 dark:border-background-700 dark:bg-background-900 dark:text-background-300 focus:border-primary-500 dark:focus:border-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 rounded-md shadow-sm">
                                @foreach ($nations as $key => $nation)
                                    <optgroup label="{{ $key }}"">
                                        @foreach ($nation as $n)
                                            <option value="{{ $n['id'] }}"
                                                {{ $n['id'] == $user->nation_id ? 'selected' : '' }}>
                                                {{ $n['name'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>



                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('users.authorization') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <div class="grid grid-cols-2 gap-2 text-background-800 dark:text-background-200"
                        x-data="{
                            selected: {{ collect($user->roles) }},
                            selectRole(role) {
                                if (!{{ collect($no_access_roles) }}.includes(role)) {
                                    if (this.selected.includes(role)) {
                                        this.selected = this.selected.filter(item => item !== role);
                                    } else {
                                        this.selected.push(role);
                                    }
                                }
                            }
                        }">

                        @foreach ($roles as $role)
                            <div x-on:click="selectRole('{{ $role->label }}')"
                                class="border border-background-700 hover:border-primary-500 rounded-lg p-4 cursor-pointer flex items-center gap-2"
                                :class="{ 'border-primary-500': selected.includes('{{ $role->label }}') }">

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

                    @if ($user->hasRole('manager'))
                        <x-user.custom-role :user="$user->id" :roleid="isset($user->customRoles->first()->id) ? $user->customRoles->first()->id : 0" />
                    @endif
                </div>


                <div class="fixed bottom-8 right-32">
                    <x-primary-button type="submit">
                        <x-lucide-save class="w-6 h-6 text-white" />
                    </x-primary-button>
                </div>

            </form>

            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 my-4"
                x-data="{}">
                <div class="flex justify-between">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('users.profile_picture') }}
                    </h3>
                    <div>
                        <form method="POST" action="{{ route('dean.users.picture.update', $user->id) }}"
                            enctype="multipart/form-data" x-ref="pfpform">
                            @csrf
                            @method('PUT')

                            <div class="flex flex-col gap-4">
                                <div class="flex flex-col gap-2">
                                    <input type="file" name="profilepicture" id="profilepicture" class="hidden"
                                        x-on:change="$refs.pfpform.submit()" />
                                    <x-primary-button type="button"
                                        onclick="document.getElementById('profilepicture').click()">
                                        {{ __('users.upload_picture') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                @if ($user->profile_picture)
                    <img src="{{ $user->profile_picture }}" alt="{{ $user->name }}" class="w-1/3 rounded-lg">
                @endif

            </div>

            <div class="grid grid-cols-2 gap-4">

                <div
                    class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8 my-4 text-background-800 dark:text-background-200 ">
                    <h3 class="text-2xl">
                        {{ __('users.academies') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                    <h5 class="text-lg">{{ __('users.as_personnel') }}</h5>

                    <div class="flex flex-col gap-2">

                        @foreach ($user->academies as $academy)
                            <a href="{{ route('academies.edit', $academy->id) }}"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $academy->name }}</span>
                            </a>
                        @endforeach

                    </div>

                    <h5 class="text-lg">{{ __('users.as_athlete') }}</h5>

                    <div class="flex flex-col gap-2">
                        @foreach ($user->academyAthletes as $academy)
                            <a href="{{ route('academies.edit', $academy->id) }}"
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
                            <a href="{{ route('schools.edit', $school->id) }}"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $school->name }}</span>
                            </a>
                        @endforeach

                    </div>

                    <h5 class="text-lg">{{ __('users.as_athlete') }}</h5>

                    <div class="flex flex-col gap-2">
                        @foreach ($user->schoolAthletes as $schools)
                            <a href="{{ route('schools.edit', $schools->id) }}"
                                class="flex flex-row items-center gap-2 hover:text-primary-500 hover:bg-background-900 p-2 rounded">
                                <x-lucide-briefcase class="w-6 h-6 text-primary-500" />
                                <span>{{ $schools->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>

            @if (!$user->is_disabled)
                <x-user.disable-user-form :user="$user->id" />
            @endif
        </div>
    </div>
</x-app-layout>
