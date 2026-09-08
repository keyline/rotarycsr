<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose Application Category — Rotary District 3291 CSR Awards</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F4F6F9] text-[#1b1b18]">
    <div class="min-h-screen flex flex-col">
        <header class="w-full border-b border-gray-200 bg-white">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <a href="{{ url('/') }}">
                    <x-site-logo class="h-10 w-auto" />
                </a>
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-[#17458F] hover:text-[#123669] transition">Log in</a>
            </div>
        </header>

        <main class="flex-1 flex items-center justify-center px-6 py-12">
            <div class="max-w-4xl w-full">
                <div class="text-center mb-10">
                    <p class="text-sm font-semibold tracking-wide text-[#F7A81B] uppercase mb-3">Rotary District 3291 — CSR Awards</p>
                    <h1 class="text-3xl sm:text-4xl font-bold text-[#17458F] mb-4">Which category are you applying under?</h1>
                    <p class="text-base text-gray-600 max-w-2xl mx-auto">
                        Choose the award category that fits you. You'll register your account next, then complete the
                        application form for the category you select — you can save your progress as a draft at any time.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <a href="{{ route('register', ['type' => 'corporate']) }}"
                       class="group block bg-white rounded-lg border border-gray-200 hover:border-[#17458F] shadow-sm hover:shadow-md transition p-8 text-left">
                        <div class="w-12 h-12 rounded-full bg-[#17458F]/10 flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#17458F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m-1 4h1m4-4h1m-1 4h1M9 21v-4a3 3 0 013-3v0a3 3 0 013 3v4" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-[#17458F] mb-2">Corporate Excellence Award</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            For corporates and foundations recognising a CSR project implemented in FY 2025-26 —
                            Small, Medium, or Large category based on annual CSR spend.
                        </p>
                        <span class="inline-flex items-center text-sm font-semibold text-[#17458F] group-hover:text-[#123669]">
                            Apply as Corporate
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </span>
                    </a>

                    <a href="{{ route('register', ['type' => 'individual']) }}"
                       class="group block bg-white rounded-lg border border-gray-200 hover:border-[#F7A81B] shadow-sm hover:shadow-md transition p-8 text-left">
                        <div class="w-12 h-12 rounded-full bg-[#F7A81B]/10 flex items-center justify-center mb-5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#F7A81B]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-[#17458F] mb-2">CSR Leader of the Year</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            For individual CSR/ESG leaders showcasing up to 3 of their most impactful projects
                            from the last 3 years.
                        </p>
                        <span class="inline-flex items-center text-sm font-semibold text-[#17458F] group-hover:text-[#123669]">
                            Apply as Individual
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </span>
                    </a>
                </div>

                <p class="text-center text-sm text-gray-500 mt-8">
                    Already registered? <a href="{{ route('login') }}" class="font-semibold text-[#17458F] hover:underline">Log in</a> to continue your application.
                </p>
            </div>
        </main>

        <footer class="w-full border-t border-gray-200 bg-white py-6">
            <p class="text-center text-xs text-gray-400">&copy; {{ date('Y') }} Rotary District 3291. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
