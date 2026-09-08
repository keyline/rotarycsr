<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} — Rotary CSR Awards</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F7F8FA] text-[#1b1b18]">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-56 shrink-0 bg-[#12325E] text-white flex flex-col">
            <div class="h-16 flex items-center px-5 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 bg-white rounded px-2 py-1">
                    <x-site-logo class="h-6 w-auto" />
                </a>
            </div>

            <nav class="flex-1 py-4 px-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.applicants.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.applicants.*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/>
                    </svg>
                    Applicants
                </a>
                <a href="{{ route('admin.activity.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.activity.*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Activity Log
                </a>
                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
            </nav>

            <div class="p-3 border-t border-white/10">
                <div class="px-3 py-2 text-xs text-white/50 truncate">{{ auth()->user()->email }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md text-sm font-medium text-white/70 hover:bg-white/5 hover:text-white transition">
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
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                <h1 class="text-lg font-semibold text-[#12325E]">{{ $title ?? 'Dashboard' }}</h1>
                <div>{{ $actions ?? '' }}</div>
            </header>

            <main class="p-6">
                @if (session('status'))
                    <div class="mb-4 px-4 py-2.5 text-sm font-medium bg-green-50 text-green-700 border border-green-200 rounded-md">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
