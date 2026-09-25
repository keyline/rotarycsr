<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-favicon />
    <title>{{ $title ?? 'Admin' }} — Rotary CSR Awards</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|manrope:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-rotary-ivory font-sans text-rotary-navy antialiased">
    <div class="flex min-h-screen flex-col overflow-x-hidden md:flex-row">
        <!-- Sidebar -->
        <aside class="flex w-full shrink-0 flex-col bg-rotary-navy text-white md:w-56">
            <div class="h-16 flex items-center px-5 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 bg-white rounded px-2 py-1">
                    <x-site-logo class="h-6 w-auto" />
                </a>
            </div>

            <nav class="flex flex-wrap gap-1 px-3 py-3 md:block md:flex-1 md:space-y-1 md:py-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex shrink-0 items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.applicants.index') }}"
                   class="flex shrink-0 items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.applicants.*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/>
                    </svg>
                    Applicants
                </a>
                <a href="{{ route('admin.activity.index') }}"
                   class="flex shrink-0 items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.activity.*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Activity Log
                </a>
                <a href="{{ route('admin.settings.index') }}"
                   class="flex shrink-0 items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
            </nav>

            <div class="flex items-center gap-2 border-t border-white/10 p-3 md:block">
                <div class="min-w-0 flex-1 truncate px-3 py-2 text-xs text-white/50">{{ auth()->user()->email }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition md:w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 min-w-0">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6">
                <h1 class="font-display text-2xl font-bold text-rotary-navy">{{ $title ?? 'Dashboard' }}</h1>
                <div>{{ $actions ?? '' }}</div>
            </header>

            <main class="p-3 sm:p-6">
                @if (session('status'))
                    <div class="mb-4 px-4 py-2.5 text-sm font-medium bg-green-50 text-green-700 border border-green-200 rounded-md">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
