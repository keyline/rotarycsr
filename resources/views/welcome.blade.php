<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rotary District 3291 CSR Awards</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F4F6F9] text-[#1b1b18]">
    <div class="min-h-screen flex flex-col">
        <header class="w-full border-b border-gray-200 bg-white">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <x-site-logo class="h-10 w-auto" />

                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-[#17458F] hover:text-[#123669] transition">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('apply') }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">Register</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-6">
            <div class="max-w-2xl w-full text-center py-16">
                <x-site-logo class="h-20 w-auto mx-auto mb-10" />

                <p class="text-sm font-semibold tracking-wide text-[#F7A81B] uppercase mb-3">Rotary District 3291</p>
                <h1 class="text-3xl sm:text-4xl font-bold text-[#17458F] mb-4">CSR Awards Application &amp; Management Portal</h1>
                <p class="text-base sm:text-lg text-gray-600 mb-10">
                    Apply for the 2nd Rotary CSR Awards under the <span class="font-semibold text-gray-800">Corporate Excellence Award</span>
                    or <span class="font-semibold text-gray-800">CSR Leader of the Year</span> category. Register to start your application,
                    save your progress as a draft, and submit before the deadline.
                </p>

                <div class="flex items-center justify-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-6 py-3 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">Go to Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-3 text-sm font-semibold text-[#17458F] border border-[#17458F] rounded-md hover:bg-[#17458F] hover:text-white transition">Log in</a>
                        <a href="{{ route('apply') }}" class="px-6 py-3 text-sm font-semibold text-white bg-[#F7A81B] rounded-md hover:bg-[#d99311] transition">Start Application</a>
                    @endauth
                </div>
            </div>
        </main>

        <footer class="w-full border-t border-gray-200 bg-white py-6">
            <p class="text-center text-xs text-gray-400">&copy; {{ date('Y') }} Rotary District 3291. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
