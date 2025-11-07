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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div class="w-full sm:max-w-md px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <a href="/" class="mb-6 p-3 items-center flex justify-center">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500"/>
                </a>

                <h2 class="flex justify-center text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Selamat Datang
                </h2>
                <h3 class="flex justify-center text-gray-900 dark:text-gray-100">
                    Di Prototipe Daisy
                </h3>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
