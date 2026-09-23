@props([
    'disabled' => false,
    'containerClass' => 'mt-1',
])

<div x-data="{ passwordVisible: false }" class="relative {{ $containerClass }}">
    <input
        @disabled($disabled)
        type="password"
        x-bind:type="passwordVisible ? 'text' : 'password'"
        {{ $attributes->merge(['class' => 'password-toggle-input w-full rounded-xl border-slate-300 bg-white/90 pr-12 shadow-sm focus:border-rotary-gold focus:ring-rotary-gold']) }}
    >

    <button
        type="button"
        class="absolute right-2 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-500 transition hover:bg-white/50 hover:text-rotary-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-rotary-gold"
        style="top: 50%; transform: translateY(-50%);"
        x-on:click="passwordVisible = ! passwordVisible"
        aria-label="{{ __('Show password') }}"
        x-bind:aria-label='passwordVisible ? @js(__('Hide password')) : @js(__('Show password'))'
        aria-pressed="false"
        x-bind:aria-pressed="passwordVisible.toString()"
        @if ($attributes->get('id')) aria-controls="{{ $attributes->get('id') }}" @endif
    >
        <svg x-show="! passwordVisible" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12S5.25 5 12 5s10.5 7 10.5 7-3.75 7-10.5 7S1.5 12 1.5 12Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <svg x-show="passwordVisible" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 12S5.25 5 12 5s10.5 7 10.5 7-3.75 7-10.5 7S1.5 12 1.5 12Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
        </svg>
        <span class="sr-only" x-text='passwordVisible ? @js(__('Hide password')) : @js(__('Show password'))'>{{ __('Show password') }}</span>
    </button>
</div>
