<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rotary District 3291 CSR Awards</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|manrope:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-rotary-ivory text-rotary-navy">
    <div class="min-h-screen flex flex-col">
        <header class="absolute inset-x-0 top-0 z-20 w-full border-b border-white/30 bg-white/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-7xl items-center justify-end px-5 py-2.5 sm:px-8">
                <x-site-logo class="h-12 w-auto sm:h-14" />
            </div>
        </header>

        <main class="flex-1">
            <section class="award-hero flex min-h-[650px] items-center pt-20 sm:min-h-[680px]" style="--award-background-image: url('{{ asset('images/csr-awards-kolkata-hero.png') }}');">
                <div class="mx-auto w-full max-w-7xl px-5 py-8 sm:px-8 sm:py-10">
                    <div class="max-w-xl">
                        <p class="award-kicker">Rotary International District 3291 presents</p>
                        <h1 class="sr-only">Rotary CSR Awards 2026</h1>
                        <img src="{{ asset('images/rotary-csr-awards-2026-tight.png') }}"
                             alt="Rotary CSR Awards 2026"
                             class="-mt-5 h-auto w-full max-w-[340px] sm:max-w-[370px]"
                             width="1246"
                             height="1263">
                        <div class="mb-4 h-1 w-16 bg-rotary-gold"></div>
                        <p class="font-display text-3xl font-semibold leading-tight text-rotary-navy sm:text-4xl">Real impact deserves recognition.</p>
                        <p class="mt-3 max-w-xl text-base leading-7 text-slate-700 sm:text-lg">A platform to honour organisations and leaders creating a stronger, more inclusive tomorrow.</p>

                        <div class="mt-6 flex flex-wrap items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="award-button">Go to Dashboard</a>
                    @else
                        <a href="{{ route('apply') }}" class="award-button-gold">Start Your Nomination</a>
                        <a href="{{ route('login') }}" class="rounded-full border border-rotary-navy/30 bg-white/70 px-6 py-3 text-sm font-bold text-rotary-navy backdrop-blur transition hover:border-rotary-navy hover:bg-white">Continue Application</a>
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

        <footer class="w-full bg-rotary-navy px-6 py-8 text-white/60">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 text-xs sm:flex-row">
                <p>&copy; {{ date('Y') }} Rotary International District 3291.</p>
                <p class="uppercase tracking-[0.2em] text-rotary-gold">Create lasting impact</p>
            </div>
        </footer>
    </div>
</body>
</html>
