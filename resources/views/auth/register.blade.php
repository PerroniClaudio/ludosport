<x-guest-layout page_title="{{ __('auth.register') }}" is_large="true">

    <form method="POST" action="{{ route('register') }}">
        @csrf

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
                    required autofocus autocomplete="birthday" />
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

                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="new-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password" />

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
                <x-form.select name="subscription_year" label="{{ __('First subscription year') }}" :options="$subYears"
                    :shouldHaveEmptyOption="false" :required="true" :value="date('Y')" />
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

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
