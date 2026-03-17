<x-guest-layout page_title="{{ __('auth.register') }}" is_large="true">

    <div x-data="{ registrationType: '{{ old('registration_type') }}' || null }" class="space-y-6">
        <div x-show="!registrationType" x-cloak
            class="mx-auto max-w-3xl rounded-2xl p-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-semibold text-background-800 dark:text-background-100">
                    {{ __('auth.registration_type_title') }}
                </h2>
                <p class="mt-3 text-sm text-background-600 dark:text-background-300">
                    {{ __('auth.registration_type_subtitle') }}
                </p>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                <button type="button" @click="registrationType = 'adult'"
                    class="group flex min-h-40 cursor-pointer flex-col justify-between rounded-2xl border border-background-200 bg-white p-6 text-left shadow-md shadow-background-200/70 transition duration-200 hover:-translate-y-1 hover:border-primary-400 hover:shadow-xl hover:shadow-primary-200/40 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-background-700 dark:bg-background-900 dark:shadow-black/20 dark:hover:shadow-primary-900/30">
                    <div>
                        <h3 class="text-xl font-semibold text-background-800 transition-colors group-hover:text-primary-600 dark:text-background-100 dark:group-hover:text-primary-400">
                            {{ __('auth.registration_adult') }}
                        </h3>
                        <p class="mt-2 text-sm text-background-600 dark:text-background-300">
                            {{ __('auth.registration_adult_description') }}
                        </p>
                    </div>
                    <span
                        class="mt-6 inline-flex w-fit items-center rounded-md border border-transparent bg-primary-500 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out duration-150 group-hover:bg-background-700 group-focus:bg-background-700 dark:bg-primary-400 dark:text-background-800 dark:group-hover:bg-primary-600 dark:group-focus:bg-primary-500">
                        {{ __('auth.choose_option') }}
                    </span>
                </button>

                <button type="button" @click="registrationType = 'minor'"
                    class="group flex min-h-40 cursor-pointer flex-col justify-between rounded-2xl border border-background-200 bg-white p-6 text-left shadow-md shadow-background-200/70 transition duration-200 hover:-translate-y-1 hover:border-primary-400 hover:shadow-xl hover:shadow-primary-200/40 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:border-background-700 dark:bg-background-900 dark:shadow-black/20 dark:hover:shadow-primary-900/30">
                    <div>
                        <h3 class="text-xl font-semibold text-background-800 transition-colors group-hover:text-primary-600 dark:text-background-100 dark:group-hover:text-primary-400">
                            {{ __('auth.registration_minor') }}
                        </h3>
                        <p class="mt-2 text-sm text-background-600 dark:text-background-300">
                            {{ __('auth.registration_minor_description') }}
                        </p>
                    </div>
                    <span
                        class="mt-6 inline-flex w-fit items-center rounded-md border border-transparent bg-primary-500 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition ease-in-out duration-150 group-hover:bg-background-700 group-focus:bg-background-700 dark:bg-primary-400 dark:text-background-800 dark:group-hover:bg-primary-600 dark:group-focus:bg-primary-500">
                        {{ __('auth.choose_option') }}
                    </span>
                </button>
            </div>
        </div>

        <div x-show="registrationType === 'adult'" x-cloak>
            <div class="mb-6 flex justify-end">
                <button type="button" @click="registrationType = null"
                    class="text-sm font-medium text-primary-600 transition hover:text-primary-500">
                    {{ __('auth.change_registration_type') }}
                </button>
            </div>

            <div class="mt-4">
                <h2 class="text-2xl font-semibold text-background-800 dark:text-background-100">
                    {{ __('auth.registration_adult') }}
                </h2>
                <p class="mt-3 text-sm text-background-600 dark:text-background-300">
                    {{ __('auth.registration_adult_description') }}
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="mt-8"
                x-data="{ isSubmitting: false, showPassword: false, showPasswordConfirmation: false }"
                @submit="isSubmitting = true">
                @csrf
                <input type="hidden" name="registration_type" value="adult">

                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('auth.personal_information') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="grid lg:grid-cols-2 gap-4 lg:w-2/3 mb-4">
                    <!-- Name -->
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"
                            required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Surname -->
                    <div>
                        <x-input-label for="surname" :value="__('Surname')" />
                        <x-text-input id="surname" class="block mt-1 w-full" type="text" name="surname" :value="old('surname')"
                            required autofocus autocomplete="surname" />
                        <x-input-error :messages="$errors->get('surname')" class="mt-2" />
                    </div>

                    <!-- Date of birth -->
                    <div>
                        <x-input-label for="birthday" :value="__('Birthday')" />
                        <x-text-input id="birthday" class="block mt-1 w-full" type="date" name="birthday" :value="old('birthday')"
                            placeholder="dd/mm/yyyy" required autofocus autocomplete="birthday" />
                        <x-input-error :messages="$errors->get('birthday')" class="mt-2" />
                    </div>

                    <!-- Gender -->

                    <div>
                        <x-form.select name="gender" label="{{ __('Gender') }}" :options="[
                            ['value' => 'male', 'label' => 'Male'],
                            ['value' => 'female', 'label' => 'Female'],
                            ['value' => 'other', 'label' => 'Other'],
                            ['value' => 'notsay', 'label' => 'Prefer not to say'],
                        ]" :shouldHaveEmptyOption="true"
                            :required="true" />
                    </div>

                </div>

                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('auth.access_information') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="grid lg:grid-cols-2 gap-4 lg:w-2/3 mb-4">

                    <!-- Email Address -->
                    <div class="col-span-2">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                            required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" />

                        <div class="relative mt-1">
                            <x-text-input id="password" class="block w-full pr-12" x-bind:type="showPassword ? 'text' : 'password'"
                                name="password" required autocomplete="new-password" />
                            <button type="button"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-background-500 transition hover:text-primary-600 focus:outline-none focus:text-primary-600"
                                x-on:click="showPassword = !showPassword"
                                x-bind:aria-label="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                                x-bind:aria-pressed="showPassword.toString()">
                                <span x-show="!showPassword" x-cloak>
                                    <x-lucide-eye class="h-5 w-5" />
                                </span>
                                <span x-show="showPassword" x-cloak>
                                    <x-lucide-eye-off class="h-5 w-5" />
                                </span>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                        <div class="relative mt-1">
                            <x-text-input id="password_confirmation" class="block w-full pr-12"
                                x-bind:type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation"
                                required autocomplete="new-password" />
                            <button type="button"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-background-500 transition hover:text-primary-600 focus:outline-none focus:text-primary-600"
                                x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                                x-bind:aria-label="showPasswordConfirmation ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                                x-bind:aria-pressed="showPasswordConfirmation.toString()">
                                <span x-show="!showPasswordConfirmation" x-cloak>
                                    <x-lucide-eye class="h-5 w-5" />
                                </span>
                                <span x-show="showPasswordConfirmation" x-cloak>
                                    <x-lucide-eye-off class="h-5 w-5" />
                                </span>
                            </button>
                        </div>

                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>


                </div>

                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('auth.additional_information') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="grid lg:grid-cols-2 gap-4 lg:w-2/3 mb-4">

                    <!-- Battle name -->
                    <div class="lg:col-span-2">
                        <x-input-label for="battle_name" :value="__('Battle name (Must not have special symbols)')" />
                        <x-text-input id="battle_name" class="block mt-1 w-full" type="text" name="battle_name"
                            :value="old('battle_name')" autofocus autocomplete="battle_name" />
                        <x-input-error :messages="$errors->get('battle_name')" class="mt-2" />
                    </div>

                    <!-- Academy -->
                    <div class="lg:col-span-2">
                        <x-form.academy-school-select selectedvalue="{{ old('academy') }}" />
                    </div>

                    <!-- Nationality -->
                    <div>
                        <x-form.nationality-select selectedvalue="{{ old('nationality') }}" />
                    </div>

                    <div>
                        @php
                            $subYears = [];
                            for ($year = 2006; $year <= date('Y'); $year++) {
                                $subYears[] = ['value' => $year, 'label' => $year];
                            }
                        @endphp
                        <x-form.select name="subscription_year" label="{{ __('Year you started LudoSport') }}"
                            :options="$subYears" :shouldHaveEmptyOption="false" :required="true" :value="date('Y')" />
                    </div>

                    <!-- How did you met us -->
                    <div>
                        <x-form.select name="how_found_us" label="{{ __('How did you meet us?') }}" :options="[
                            ['value' => 'facebook', 'label' => 'Facebook'],
                            ['value' => 'instagram', 'label' => 'Instagram'],
                            ['value' => 'twitter', 'label' => 'Twitter'],
                            ['value' => 'youtube', 'label' => 'Youtube'],
                            ['value' => 'website', 'label' => 'Website'],
                            ['value' => 'friend', 'label' => 'Friend'],
                            ['value' => 'event', 'label' => 'Event'],
                            ['value' => 'flyer', 'label' => 'Flyer'],
                            // ['value' => 'other', 'label' => 'Other'],
                        ]"
                            :shouldHaveEmptyOption="true" :required="true" />
                    </div>



                </div>

                <div class="flex items-center justify-end mt-4">
                    <a class="underline text-sm text-background-600 dark:text-background-400 hover:text-background-900 dark:hover:text-background-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-background-800"
                        href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <x-primary-button class="ms-4" x-bind:disabled="isSubmitting" x-bind:aria-busy="isSubmitting">
                        <span x-show="!isSubmitting" x-cloak>{{ __('Register') }}</span>
                        <span x-show="isSubmitting" x-cloak>{{ __('auth.registering') }}</span>
                    </x-primary-button>
                </div>
            </form>
        </div>

        <div x-show="registrationType === 'minor'" x-cloak>
            <div class="flex justify-end">
                <button type="button" @click="registrationType = null"
                    class="text-sm font-medium text-primary-600 transition hover:text-primary-500">
                    {{ __('auth.change_registration_type') }}
                </button>
            </div>

            <div class="mt-4">
                <h2 class="text-2xl font-semibold text-background-800 dark:text-background-100">
                    {{ __('auth.registration_minor') }}
                </h2>
                <p class="mt-3 text-sm text-background-600 dark:text-background-300">
                    {{ __('auth.registration_minor_description') }}
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="mt-8"
                x-data="{
                    isSubmitting: false,
                    showPassword: false,
                    showPasswordConfirmation: false,
                    minorDocumentsError: '',
                    validateMinorDocuments(event) {
                        const file = event.target.files?.[0];
                        const maxSize = 10 * 1024 * 1024;

                        if (!file) {
                            this.minorDocumentsError = '';
                            return;
                        }

                        if (file.size > maxSize) {
                            this.minorDocumentsError = '{{ __('auth.minor_documents_too_large') }}';
                            event.target.value = '';
                            return;
                        }

                        this.minorDocumentsError = '';
                    }
                }"
                @submit="if (minorDocumentsError) { return false; } isSubmitting = true">
                @csrf
                <input type="hidden" name="registration_type" value="minor">

                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('auth.personal_information') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="grid lg:grid-cols-2 gap-4 lg:w-2/3 mb-4">
                    <div>
                        <x-input-label for="minor_name" :value="__('Name')" />
                        <x-text-input id="minor_name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"
                            required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="minor_surname" :value="__('Surname')" />
                        <x-text-input id="minor_surname" class="block mt-1 w-full" type="text" name="surname"
                            :value="old('surname')" required autofocus autocomplete="surname" />
                        <x-input-error :messages="$errors->get('surname')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="minor_birthday" :value="__('Birthday')" />
                        <x-text-input id="minor_birthday" class="block mt-1 w-full" type="date" name="birthday"
                            :value="old('birthday')" placeholder="dd/mm/yyyy" required autofocus autocomplete="birthday" />
                        <x-input-error :messages="$errors->get('birthday')" class="mt-2" />
                    </div>

                    <div>
                        <x-form.select name="gender" label="{{ __('Gender') }}" :options="[
                            ['value' => 'male', 'label' => 'Male'],
                            ['value' => 'female', 'label' => 'Female'],
                            ['value' => 'other', 'label' => 'Other'],
                            ['value' => 'notsay', 'label' => 'Prefer not to say'],
                        ]" :shouldHaveEmptyOption="true"
                            :required="true" />
                    </div>
                </div>

                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('auth.access_information') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="grid lg:grid-cols-2 gap-4 lg:w-2/3 mb-4">
                    <div class="col-span-2">
                        <x-input-label for="minor_email" :value="__('Email')" />
                        <x-text-input id="minor_email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                            required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="minor_password" :value="__('Password')" />
                        <div class="relative mt-1">
                            <x-text-input id="minor_password" class="block w-full pr-12"
                                x-bind:type="showPassword ? 'text' : 'password'" name="password" required
                                autocomplete="new-password" />
                            <button type="button"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-background-500 transition hover:text-primary-600 focus:outline-none focus:text-primary-600"
                                x-on:click="showPassword = !showPassword"
                                x-bind:aria-label="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                                x-bind:aria-pressed="showPassword.toString()">
                                <span x-show="!showPassword" x-cloak>
                                    <x-lucide-eye class="h-5 w-5" />
                                </span>
                                <span x-show="showPassword" x-cloak>
                                    <x-lucide-eye-off class="h-5 w-5" />
                                </span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="minor_password_confirmation" :value="__('Confirm Password')" />
                        <div class="relative mt-1">
                            <x-text-input id="minor_password_confirmation" class="block w-full pr-12"
                                x-bind:type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation"
                                required autocomplete="new-password" />
                            <button type="button"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-background-500 transition hover:text-primary-600 focus:outline-none focus:text-primary-600"
                                x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                                x-bind:aria-label="showPasswordConfirmation ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                                x-bind:aria-pressed="showPasswordConfirmation.toString()">
                                <span x-show="!showPasswordConfirmation" x-cloak>
                                    <x-lucide-eye class="h-5 w-5" />
                                </span>
                                <span x-show="showPasswordConfirmation" x-cloak>
                                    <x-lucide-eye-off class="h-5 w-5" />
                                </span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>

                <h3 class="text-background-800 dark:text-background-200 text-2xl">{{ __('auth.additional_information') }}</h3>
                <div class="border-b border-background-100 dark:border-background-700 my-2"></div>

                <div class="grid lg:grid-cols-2 gap-4 lg:w-2/3 mb-4">
                    <div class="lg:col-span-2">
                        <x-input-label for="minor_battle_name" :value="__('Battle name (Must not have special symbols)')" />
                        <x-text-input id="minor_battle_name" class="block mt-1 w-full" type="text" name="battle_name"
                            :value="old('battle_name')" autofocus autocomplete="battle_name" />
                        <x-input-error :messages="$errors->get('battle_name')" class="mt-2" />
                    </div>

                    <div class="lg:col-span-2">
                        <x-form.academy-school-select selectedvalue="{{ old('academy') }}" />
                    </div>

                    <div>
                        <x-form.nationality-select selectedvalue="{{ old('nationality') }}" />
                    </div>

                    <div>
                        @php
                            $subYears = [];
                            for ($year = 2006; $year <= date('Y'); $year++) {
                                $subYears[] = ['value' => $year, 'label' => $year];
                            }
                        @endphp
                        <x-form.select name="subscription_year" label="{{ __('Year you started LudoSport') }}"
                            :options="$subYears" :shouldHaveEmptyOption="false" :required="true" :value="date('Y')" />
                    </div>

                    <div>
                        <x-form.select name="how_found_us" label="{{ __('How did you meet us?') }}" :options="[
                            ['value' => 'facebook', 'label' => 'Facebook'],
                            ['value' => 'instagram', 'label' => 'Instagram'],
                            ['value' => 'twitter', 'label' => 'Twitter'],
                            ['value' => 'youtube', 'label' => 'Youtube'],
                            ['value' => 'website', 'label' => 'Website'],
                            ['value' => 'friend', 'label' => 'Friend'],
                            ['value' => 'event', 'label' => 'Event'],
                            ['value' => 'flyer', 'label' => 'Flyer'],
                        ]"
                            :shouldHaveEmptyOption="true" :required="true" />
                    </div>

                    <div class="lg:col-span-2">
                        <x-input-label for="minor_documents" :value="__('auth.minor_documents')" />
                        <p class="mt-1 text-sm text-background-600 dark:text-background-300">
                            {{ __('auth.minor_documents_description') }}
                        </p>
                        <input id="minor_documents" name="minor_documents" type="file"
                            @change="validateMinorDocuments($event)"
                            class="mt-3 block w-full rounded-lg border border-background-200 bg-white px-4 py-3 text-sm text-background-700 shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:border-background-700 dark:bg-background-900 dark:text-background-200 dark:file:bg-primary-950/50 dark:file:text-primary-300" />
                        <p class="mt-2 text-xs text-background-500 dark:text-background-400">
                            {{ __('auth.minor_documents_help') }}
                        </p>
                        <p x-show="minorDocumentsError" x-text="minorDocumentsError"
                            class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
                        <x-input-error :messages="$errors->get('minor_documents')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <a class="underline text-sm text-background-600 dark:text-background-400 hover:text-background-900 dark:hover:text-background-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-background-800"
                        href="{{ route('login') }}">
                        {{ __('Already registered?') }}
                    </a>

                    <x-primary-button class="ms-4" x-bind:disabled="isSubmitting" x-bind:aria-busy="isSubmitting">
                        <span x-show="!isSubmitting" x-cloak>{{ __('Register') }}</span>
                        <span x-show="isSubmitting" x-cloak>{{ __('auth.registering') }}</span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
