<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-background-100 dark:bg-background-900">
        <!-- Page Content -->
        <main class="flex flex-col min-h-screen items-center justify-center">
            <form method="POST" action="{{ route('profile.institution.update') }}"
                class="bg-white dark:bg-background-800 overflow-hidden shadow-sm sm:rounded-lg w-full sm:max-w-md lg:max-w-xl "
                x-data="{
                    selected: null,
                }">
                @csrf
                <div class="p-6 text-background-900 dark:text-background-100">
                    <h3 class="text-background-800 dark:text-background-200 text-2xl">
                        {{ __('users.select_institution') }}
                    </h3>
                    <div class="border-b border-background-100 dark:border-background-700 my-2"></div>
                    <p class="text-lg mb-2">{{ __('users.select_institution_tooltip') }}</p>

                    <div class="flex flex-col gap-2">

                        @foreach ($academies as $academy)
                            <div x-on:click="selected = '{{ $academy->id }}'"
                                class="border border-background-700 hover:border-primary-500 rounded-lg p-4 cursor-pointer flex items-center gap-2"
                                :class="{ 'border-primary-500': selected == '{{ $academy->id }}' }">
                                <x-lucide-building class="w-6 h-6 text-primary-500" />
                                <span class="text-background-800 dark:text-background-200">
                                    {{ $academy->name }}
                                </span>
                            </div>
                        @endforeach

                        @foreach ($schools as $school)
                            <div x-on:click="selected = '{{ $school->id }}'"
                                class="border border-background-700 hover:border-primary-500 rounded-lg p-4 cursor-pointer flex items-center gap-2"
                                :class="{ 'border-primary-500': selected == '{{ $school->id }}' }">
                                <x-lucide-building class="w-6 h-6 text-primary-500" />
                                <span class="text-background-800 dark:text-background-200">
                                    {{ $school->name }}
                                </span>
                            </div>
                        @endforeach

                    </div>

                    <input type="hidden" name="institution_id" x-model="selected">

                    <div class="flex justify-end mt-4">
                        <button type="submit" :disabled="selected === null"
                            class="bg-primary-500 hover:bg-primary-600 text-white font-bold py-2 px-4 rounded cursor-pointer  focus:outline-none focus:shadow-outline disabled:cursor-not-allowed disabled:pointer-events-none disabled:opacity-60 ">
                            {{ __('users.continue') }}
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <x-flash />
</body>

</html>
