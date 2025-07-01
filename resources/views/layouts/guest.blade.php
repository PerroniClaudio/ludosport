@props([
    'page_title' => '',
    'is_large' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ route('favicon') }}" type="image/x-icon">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-background-900 antialiased">
    <div
        class="min-h-screen flex flex-col sm:justify-center items-center py-6 bg-background-100 dark:bg-background-900">
        <div>
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-background-500" />
            </a>
        </div>

        <div class="w-full text-left sm:max-w-md lg:max-w-7xl">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ $page_title }}
            </h1>
        </div>

        @if ($is_large)
            <div
                class="w-full sm:max-w-md lg:max-w-7xl mt-6 mb-6 px-6 py-4 bg-white dark:bg-background-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        @else
            <div
                class="w-full sm:max-w-md  mt-6 mb-6 px-6 py-4 bg-white dark:bg-background-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        @endif
    </div>

    {{-- Cookie Policy e Privacy Policy banner --}}
    <x-policy-banner />

</body>

</html>
