<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <title>Rotary District 3291 CSR Awards</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|manrope:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-rotary-ivory text-rotary-navy">
    <div class="min-h-screen flex flex-col">
        <header x-data="{ open: false }" class="relative z-20 w-full bg-white sm:absolute sm:inset-x-0 sm:top-0 sm:bg-transparent">
            <div class="flex min-h-[92px] items-center justify-between gap-3 px-5 py-2 sm:hidden">
                <a href="{{ url('/') }}" class="flex min-w-0 flex-col items-start" aria-label="Rotary District 3291 home">
                    <x-site-logo class="block h-auto w-[225px] max-w-full" />
                    <span class="pl-1 text-xs font-extrabold leading-none text-slate-900">District 3291</span>
                </a>
                <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-controls="home-mobile-menu" aria-label="Toggle navigation" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-rotary-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-rotary-gold">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path x-show="! open" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-cloak d="M5 5l14 14M19 5L5 19" />
                    </svg>
                </button>
            </div>
            <nav id="home-mobile-menu" x-show="open" x-cloak class="absolute inset-x-0 top-full border-t border-rotary-gold/20 bg-white px-5 py-3 shadow-lg sm:hidden" aria-label="Mobile navigation">
                <a href="{{ url('/') }}" class="block rounded-lg px-3 py-3 text-sm font-semibold text-rotary-navy hover:bg-rotary-ivory">Home</a>
                <a href="{{ route('apply') }}" class="block rounded-lg px-3 py-3 text-sm font-semibold text-rotary-navy hover:bg-rotary-ivory">Start Your Nomination</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-3 text-sm font-semibold text-rotary-navy hover:bg-rotary-ivory">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block rounded-lg px-3 py-3 text-sm font-semibold text-rotary-navy hover:bg-rotary-ivory">Continue Application</a>
                @endauth
            </nav>
            <div class="hidden w-full items-center justify-end px-5 pt-5 sm:flex sm:px-8 lg:pt-20">
                <x-site-logo class="h-auto w-40 shrink-0 sm:w-72 lg:w-96" />
            </div>
        </header>

        <main class="flex-1">
            <section class="award-hero home-hero relative flex min-h-[calc(100svh-92px)] items-start sm:min-h-[680px] sm:items-center sm:pt-40 lg:pt-32" style="--award-background-image: url('{{ asset('images/csr-awards-kolkata-hero.png') }}');">
                <div class="relative z-10 mx-auto w-full max-w-7xl px-5 pb-44 pt-7 sm:px-8 sm:py-10">
                    <div class="mx-auto flex max-w-xl flex-col items-center text-center sm:mx-0 sm:block sm:text-left">
                        <h1 class="sr-only">Rotary CSR Awards 2026</h1>
                        <p class="max-w-[290px] text-sm font-bold uppercase leading-snug tracking-[0.08em] text-[#ad7c28] sm:mb-2 sm:max-w-none sm:pl-2 sm:text-[11px] sm:tracking-[0.12em] sm:text-rotary-gold">
                            Rotary International District 3291 presents
                        </p>
                        <img src="{{ asset('images/rotary-csr-awards-2026-tight.png') }}"
                             alt="Rotary CSR Awards 2026"
                             class="-mt-2 h-auto w-full max-w-[245px] sm:-mt-5 sm:max-w-[370px]"
                             width="1246"
                             height="1263">
                        <div class="mb-4 hidden h-1 w-16 bg-rotary-gold sm:block"></div>
                        <p class="max-w-sm font-display text-[2rem] font-semibold leading-[1.05] text-rotary-navy sm:max-w-none sm:text-4xl sm:leading-tight">Real impact deserves recognition.</p>
                        <p class="mt-3 max-w-[330px] text-base leading-6 text-slate-700 sm:max-w-xl sm:leading-7 sm:text-lg">A platform to honour organisations and leaders creating a stronger, more inclusive tomorrow.</p>

                        <div class="mt-6 flex w-full max-w-[330px] flex-col gap-3 sm:max-w-none sm:flex-row sm:flex-wrap sm:items-center sm:gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="award-button w-full py-4 text-base sm:w-auto sm:py-3 sm:text-sm">Go to Dashboard</a>
                            @else
                                <a href="{{ route('apply') }}" class="award-button-gold w-full py-4 text-base sm:w-auto sm:py-3 sm:text-sm">Start Your Nomination <svg class="ml-2 h-5 w-5 sm:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h16m-7-7 7 7-7 7" /></svg></a>
                                <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-full border border-rotary-navy/50 bg-white/80 px-6 py-4 text-base font-bold text-rotary-navy backdrop-blur transition hover:border-rotary-navy hover:bg-white sm:w-auto sm:border-rotary-navy/30 sm:bg-white/70 sm:py-3 sm:text-sm">Continue Application</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </section>

            <!-- <section class="bg-rotary-ivory px-5 py-20 sm:px-8">
                <div class="mx-auto max-w-6xl">
                    <div class="text-center">
                        <p class="award-rule award-kicker">Award Categories</p>
                        <h2 class="mt-5 font-display text-4xl font-bold sm:text-5xl">Two paths. One shared purpose.</h2>
                    </div>
                    <div class="mt-12 grid gap-6 md:grid-cols-2">
                        <a href="{{ route('register', ['type' => 'individual']) }}" class="group award-card relative overflow-hidden bg-rotary-navy p-8 text-white sm:p-10">
                            <div class="flex items-center gap-5">
                                <img src="{{ asset('images/category-leader-navy.png') }}"
                                     alt="Corporate CSR Leader category icon"
                                     class="h-20 w-20 shrink-0 rounded-full object-cover ring-1 ring-rotary-gold/40"
                                     width="80"
                                     height="80">
                                <div class="min-w-0">
                                    <span class="text-sm font-bold uppercase tracking-[0.2em] text-rotary-gold">Category I</span>
                                    <h3 class="mt-1 font-display text-3xl font-semibold leading-tight sm:text-4xl">Corporate CSR Leader</h3>
                                </div>
                            </div>
                            <p class="mt-6 max-w-md text-sm leading-6 text-white/70">Recognising individual CSR and ESG leaders whose vision has built measurable, lasting impact.</p>
                            <span class="mt-8 inline-flex items-center gap-2 text-sm font-bold text-rotary-gold">Apply as an individual <span class="transition group-hover:translate-x-1">→</span></span>
                        </a>
                        <a href="{{ route('register', ['type' => 'corporate']) }}" class="group award-card relative overflow-hidden bg-rotary-navy p-8 text-white sm:p-10">
                            <div class="flex items-center gap-5">
                                <img src="{{ asset('images/category-corporate-navy.png') }}"
                                     alt="CSR Project Excellence category icon"
                                     class="h-20 w-20 shrink-0 rounded-full object-cover ring-1 ring-rotary-gold/40"
                                     width="80"
                                     height="80">
                                <div class="min-w-0">
                                    <span class="text-sm font-bold uppercase tracking-[0.2em] text-rotary-gold">Category II</span>
                                    <h3 class="mt-1 font-display text-3xl font-semibold leading-tight sm:text-4xl">CSR Project Excellence</h3>
                                </div>
                            </div>
                            <p class="mt-6 max-w-md text-sm leading-6 text-white/70">Honouring organisations and foundations delivering exemplary projects across Rotary's areas of focus.</p>
                            <span class="mt-8 inline-flex items-center gap-2 text-sm font-bold text-rotary-gold">Apply as an organisation <span class="transition group-hover:translate-x-1">→</span></span>
                        </a>
                    </div>
                </div>
            </section> -->
        </main>

        <x-site-footer />
    </div>
</body>
</html>
