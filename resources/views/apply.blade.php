<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <title>Choose Application Category — Rotary District 3291 CSR Awards</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|manrope:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-rotary-ivory text-rotary-navy">
    <div class="min-h-screen flex flex-col">
        <header class="w-full border-b border-rotary-gold/20 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
                <a href="{{ url('/') }}">
                    <x-site-logo class="h-auto w-44 sm:w-80" />
                </a>
                <a href="{{ route('login') }}" class="rounded-full border border-rotary-navy/20 px-5 py-2.5 text-sm font-bold text-rotary-navy transition hover:bg-rotary-navy hover:text-white">Log in</a>
            </div>
        </header>

        <main class="award-sky-panel flex flex-1 items-center justify-center px-5 py-16 sm:px-8" style="--award-background-image: url('{{ asset('images/csr-awards-kolkata-hero.png') }}');">
            <div class="w-full max-w-5xl">
                <div class="mb-10 text-center text-white">
                    <p class="award-kicker mb-3" style="font-size: 17px;">Rotary CSR Awards 2026</p>
                    <h1 class="font-display text-4xl font-bold sm:text-6xl">Choose your award category</h1>
                    <p class="mx-auto mt-4 max-w-2xl text-base leading-7 text-white/75 sm:text-lg">
                        Choose the award category that fits you. You'll register your account next, then complete the
                        application form for the category you select — you can save your progress as a draft at any time.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <a href="{{ route('register', ['type' => 'corporate']) }}"
                       class="group award-card flex flex-col p-6 text-left transition duration-300 hover:-translate-y-1 hover:border-rotary-gold sm:p-7">
                        <div class="flex items-center gap-5">
                            <img src="{{ asset('images/category-corporate-navy.png') }}"
                                 alt="CSR Corporate Excellence"
                                 class="h-20 w-20 shrink-0 rounded-full object-cover ring-1 ring-rotary-gold/30"
                                 width="80"
                                 height="80">
                            <div class="min-w-0">
                                <p class="award-kicker">Category A</p>
                                <h2 class="mt-1 font-display text-2xl font-bold leading-tight text-rotary-navy sm:text-[1.7rem]">CSR Corporate Excellence</h2>
                            </div>
                        </div>
                        <p class="mt-5 text-base leading-7 text-slate-600">
                            For corporates and foundations recognising a CSR project implemented in FY 2025-26 —
                            micro, macro & mega category based on annual CSR spend.
                        </p>
                        <span class="mt-auto inline-flex items-center pt-5 text-base font-bold text-rotary-blue">
                            Apply as an organisation
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </span>
                    </a>
                    
                    <a href="{{ route('register', ['type' => 'individual']) }}"
                       class="group award-card flex flex-col p-6 text-left transition duration-300 hover:-translate-y-1 hover:border-rotary-gold sm:p-7">
                        <div class="flex items-center gap-5">
                            <img src="{{ asset('images/category-leader-navy.png') }}"
                                 alt="Corporate CSR Leader"
                                 class="h-20 w-20 shrink-0 rounded-full object-cover ring-1 ring-rotary-gold/30"
                                 width="80"
                                 height="80">
                            <div class="min-w-0">
                                <p class="award-kicker">Category B</p>
                                <h2 class="mt-1 font-display text-2xl font-bold leading-tight text-rotary-navy sm:text-[1.7rem]">Corporate CSR Leader</h2>
                            </div>
                        </div>
                        <p class="mt-5 text-base leading-7 text-slate-600">
                            For individual CSR/ESG leaders showcasing up to 3 of their most impactful projects
                            from the last 3 years.
                        </p>
                        <span class="mt-auto inline-flex items-center pt-5 text-base font-bold text-rotary-blue">
                            Apply as an individual
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </span>
                    </a>                    
                </div>

                <p class="mt-8 text-center text-base text-white/70">
                    Already registered? <a href="{{ route('login') }}" class="font-bold text-white underline decoration-rotary-gold underline-offset-4">Log in</a> to continue your application.
                </p>
            </div>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
