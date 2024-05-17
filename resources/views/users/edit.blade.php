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
                            @if ($user->role == 'user')
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

            <form method="POST" action="{{ route('users.update', $user->id) }}" class="flex flex-col gap-4 mb-4">
                @csrf

                <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('users.personal_details_message') }}</h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <div class="w-1/2 flex flex-col gap-2">
                        <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                            value="{{ $user->name }}" placeholder="{{ fake()->firstName() }}" />
                        <x-form.input name="surname" label="Surname" type="text" required="{{ true }}"
                            value="{{ $user->surname }}" placeholder="{{ fake()->lastName() }}" />
                        <x-form.input name="email" label="Email" type="email" required="{{ true }}"
                            value="{{ $user->email }}" placeholder="{{ fake()->email() }}" />
                        <x-form.input name="year" label="Year" type="text" required="{{ true }}"
                            value="{{ $user->subscription_year }}" placeholder="{{ date('Y') }}" />
                    </div>
                </div>

                <x-user.provenance-selector nationality="{{ $user->nation_id }}"
                    selectedAcademyId="{{ $user->academy_id }}"
                    selectedAcademy="{{ $user->academy ? $user->academy->name : '' }}"
                    selectedSchoolId="{{ $user->school_id }}"
                    selectedSchool="{{ $user->school ? $user->school->name : '' }}" :academies="$academies" :nations="$nations"
                    :schools="$schools" />

                @if ($user->role != 'admin')
                    <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                        <h3 class="text-background-800 dark:text-background-200 text-2xl">
                            {{ __('users.authorization') }}
                        </h3>
                        <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                        <div class="w-1/2 flex flex-col gap-2">
                            <x-form.select name="role" label="Role" required="{{ true }}"
                                :options="$roles" value="{{ $user->role }}" />
                        </div>
                    </div>
                @endif


                <div class="fixed bottom-8 right-32">
                    <x-primary-button type="submit">
                        <x-lucide-save class="w-6 h-6 text-white" />
                    </x-primary-button>
                </div>

            </form>

            @if (!$user->is_disabled)
                <x-user.disable-user-form :user="$user->id" />
            @endif
        </div>
    </div>
</x-app-layout>
