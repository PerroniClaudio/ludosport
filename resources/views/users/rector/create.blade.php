<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-background-800 dark:text-background-200 leading-tight">
                {{ __('New User') }}
            </h2>

        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form action="{{ route('rector.users.store') }}" method="POST" enctype="multipart/form-data"
                    x-data="{ isMinor: @js((bool) old('is_user_minor')) }">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                        <div class="flex flex-col gap-2">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('users.personal_details_message') }}</h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                            <x-form.input name="name" label="Name" type="text" required="{{ true }}"
                                value="{{ old('name') }}" placeholder="{!! fake()->firstName() !!}" />
                            <x-form.input name="surname" label="Surname" type="text" required="{{ true }}"
                                value="{{ old('surname') }}" placeholder="{!! fake()->lastName() !!}" />
                            <x-form.input name="email" label="Email" type="email" required="{{ true }}"
                                value="{{ old('email') }}" placeholder="{{ fake()->email() }}" />
                            <x-form.input name="year" label="First subscription year" type="text"
                                required="{{ true }}" value="{{ old('year') }}"
                                placeholder="{{ date('Y') }}" />
                            <small
                                class="text-background-800 dark:text-background-200"><i>{{ __('users.password_creation_message') }}</i></small>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('users.provenance') }}</h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                            <x-form.nationality-select selectedvalue="{{ old('nationality') }}" />
                            <x-form.academy-select :academies="$academies" />

                            <label
                                class="mt-2 flex items-center gap-3 rounded-lg border border-background-200 dark:border-background-700 px-4 py-3 text-sm text-background-800 dark:text-background-200">
                                <input type="checkbox" name="is_user_minor" value="1" x-model="isMinor"
                                    @checked(old('is_user_minor'))
                                    class="rounded border-background-300 text-primary-600 focus:ring-primary-500">
                                <span>{{ __('Minor user') }}</span>
                            </label>
                        </div>

                        <div class="flex flex-col gap-2">
                            <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                {{ __('users.authorization') }}</h3>
                            <div class="border-b border-background-100 dark:border-background-700 my-2"></div>


                            <div class="flex flex-col gap-2 text-background-800 dark:text-background-200"
                                x-data="{
                                    selected: [],
                                    selectRole(role) {
                                        if (this.selected.includes(role)) {
                                            this.selected = this.selected.filter(item => item !== role);
                                        } else {
                                            this.selected.push(role);
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
                        </div>

                    </div>

                    <div x-show="isMinor" x-cloak class="mt-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-2">
                                <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                    {{ __('Minor details') }}</h3>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                                <x-form.input name="birthday" label="Birthday" type="date" required="{{ false }}"
                                    value="{{ old('birthday') }}" />
                                <x-form.select name="gender" label="{{ __('Gender') }}" :options="[
                                    ['value' => 'male', 'label' => 'Male'],
                                    ['value' => 'female', 'label' => 'Female'],
                                    ['value' => 'other', 'label' => 'Other'],
                                    ['value' => 'notsay', 'label' => 'Prefer not to say'],
                                ]" :shouldHaveEmptyOption="true" />
                            </div>

                            <div class="flex flex-col gap-2 sm:col-span-2">
                                <h3 class="text-background-800 dark:text-background-200 text-2xl">
                                    {{ __('Minor documents') }}</h3>
                                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                                <label for="minor_documents"
                                    class="text-sm font-medium text-background-700 dark:text-background-300">
                                    {{ __('auth.minor_documents') }}
                                </label>
                                <p class="text-sm text-background-600 dark:text-background-400">
                                    {{ __('auth.minor_documents_description') }}
                                </p>
                                <input id="minor_documents" name="minor_documents" type="file"
                                    accept="application/pdf"
                                    class="block w-full rounded-lg border border-background-200 bg-white px-4 py-3 text-sm text-background-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:border-background-700 dark:bg-background-900 dark:text-background-200 dark:file:bg-primary-950/50 dark:file:text-primary-300" />
                                <p class="text-xs text-background-500 dark:text-background-400">
                                    {{ __('auth.minor_documents_help') }}
                                </p>
                                <x-input-error :messages="$errors->get('minor_documents')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 mt-8">
                        <x-secondary-button type="reset">
                            {{ __('users.cancel') }}
                        </x-secondary-button>
                        <x-primary-button type="submit">
                            {{ __('users.create') }}
                        </x-primary-button>
                    </div>


                </form>

                @if ($errors->any())
                    <div class="mt-4 text-red-600">
                        <p>
                            {{ $errors->first() }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>
