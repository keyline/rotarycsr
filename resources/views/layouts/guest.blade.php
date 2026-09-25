<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-favicon />

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|manrope:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col">
            <main class="award-sky-panel flex flex-1 flex-col items-center justify-center px-4 py-10" style="--award-background-image: url('{{ asset('images/csr-awards-kolkata-hero.png') }}');">
                <div>
                    <a href="/">
                        <x-site-logo class="block h-auto w-72 max-w-full rounded-xl bg-white/95 px-4 py-3 shadow-lg sm:w-96" />
                    </a>
                </div>

                <div class="award-card mt-7 w-full px-4 py-6 sm:max-w-md sm:px-8 sm:py-7">
                    {{ $slot }}
                </div>
            </main>

            <x-site-footer />
        </div>
    </body>
</html>
