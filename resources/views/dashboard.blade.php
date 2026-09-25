@php
    $user = auth()->user();
    $isCorporate = $application->applicant_type === 'corporate';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-3xl font-bold leading-tight text-rotary-navy">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10 sm:py-14">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="px-4 py-3 text-sm font-medium bg-green-50 text-green-700 border border-green-200 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Welcome -->
            <div class="award-card p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <p class="award-kicker mb-2">Rotary CSR Awards 2026</p>
                        <h3 class="font-display text-3xl font-bold text-rotary-navy">
                            Welcome, {{ $user->name }}
                        </h3>
                        <p class="mt-2 text-base leading-7 text-gray-600">
                            ROTARY INTERNATIONAL DISTRICT 3291 welcomes you to apply for the
                            <span class="font-semibold text-gray-800">{{ $isCorporate ? 'CSR Project Excellence Award' : 'Corporate CSR Leader Award' }}</span>
                            for your Company’s outstanding CSR Project.
                        </p>
                        <p class="mt-3 text-base leading-7 text-gray-600">
                            The event will be held in November 2026 in Kolkata to recognize distinguished Corporates and eminent CSR Leaders.
                        </p>
                    </div>
                    <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full {{ $isCorporate ? 'bg-[#17458F]/10 text-[#17458F]' : 'bg-[#F7A81B]/10 text-[#a4700f]' }}">
                        {{ $isCorporate ? 'Corporate' : 'Individual' }}
                    </span>
                </div>
            </div>

            @unless ($application->isSubmitted())
                <!-- Deadline -->
                @if ($deadline)
                    <div class="px-4 py-3 text-sm rounded-md border {{ $deadlinePassed ? 'bg-red-50 text-red-700 border-red-200' : 'bg-[#17458F]/5 text-[#17458F] border-[#17458F]/20' }}">
                        @if ($deadlinePassed)
                            The submission window closed on {{ \Illuminate\Support\Carbon::parse($deadline)->format('d M Y, h:i A') }}. No further edits or submissions are possible.
                        @else
                            Submissions close on <span class="font-semibold">{{ \Illuminate\Support\Carbon::parse($deadline)->format('d M Y, h:i A') }}</span>
                            ({{ \Illuminate\Support\Carbon::parse($deadline)->diffForHumans() }}).
                        @endif
                    </div>
                @endif
            @endunless

            @if ($user->isBlacklisted())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <span class="font-bold">Account blacklisted.</span>
                    You cannot edit this application or participate in future award cycles with this account.
                </div>
            @endif

            @unless ($application->isSubmitted())
                <!-- Instructions -->
                <div class="award-card p-6 sm:p-8">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">How it works</h3>
                    <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
                        <li>Complete the application in short, guided steps — your answers are saved automatically as you type.</li>
                        <li>You can leave at any point and pick up right where you left off, right up until the deadline.</li>
                        <li>Review everything on the final step before submitting.</li>
                        <li>Once submitted, the application is locked and cannot be edited further.</li>
                    </ol>
                </div>
            @endunless

            <!-- Application status -->
            <div class="award-card p-6 sm:p-8">
                @if ($application->isSubmitted())
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-green-700">Application successfully received</p>
                            <h3 class="mt-1 font-display text-2xl font-bold text-rotary-navy">Thank you for your submission, {{ $user->name }}.</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600">
                                We have successfully received your application for the
                                <span class="font-semibold text-gray-800">{{ $isCorporate ? 'Corporate Excellence Award' : 'CSR Leader of the Year' }}</span>.
                                The Rotary District 3291 CSR Awards team will review your submission and share any updates with you by email.
                            </p>
                            <p class="mt-3 text-xs font-medium text-gray-500">
                                Submitted on {{ $application->submitted_at->format('d M Y, h:i A') }}. Your application is now read-only.
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 border-t border-gray-100 pt-5">
                        <a href="{{ route('application.review') }}" class="inline-block px-5 py-2.5 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">
                            View Submitted Application
                        </a>
                    </div>
                @else
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-800">Application Progress</p>
                        <span class="text-xs text-gray-500">{{ $application->progressPercent() }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 mb-5">
                        <div class="bg-[#17458F] h-2 rounded-full transition-all" style="width: {{ $application->progressPercent() }}%"></div>
                    </div>

                    @if ($user->isBlacklisted())
                        <p class="text-sm font-medium text-red-700">This application is read-only because the account is blacklisted.</p>
                    @elseif ($deadlinePassed)
                        <p class="text-sm text-red-600">The submission deadline has passed. You can no longer continue this application.</p>
                    @else
                        <a href="{{ route('application.step', min($application->current_step, $application->totalSteps())) }}"
                           class="award-button-gold">
                            {{ $application->current_step > 1 ? 'Continue Application' : 'Start Application' }}
                        </a>
                    @endif
                @endif
            </div>

            
        </div>
    </div>
</x-app-layout>
