@php
    $user = auth()->user();
    $isCorporate = $application->applicant_type === 'corporate';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="px-4 py-3 text-sm font-medium bg-green-50 text-green-700 border border-green-200 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Welcome -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div>
                        <h3 class="text-lg font-bold text-[#17458F]">
                            Welcome, {{ $user->name }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            You're applying for the <span class="font-semibold text-gray-700">{{ $isCorporate ? 'Corporate Excellence Award' : 'CSR Leader of the Year' }}</span> category
                            of the Rotary District 3291 CSR Awards.
                        </p>
                    </div>
                    <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full {{ $isCorporate ? 'bg-[#17458F]/10 text-[#17458F]' : 'bg-[#F7A81B]/10 text-[#a4700f]' }}">
                        {{ $isCorporate ? 'Corporate' : 'Individual' }}
                    </span>
                </div>
            </div>

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

            <!-- Instructions -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">How it works</h3>
                <ol class="space-y-2 text-sm text-gray-600 list-decimal list-inside">
                    <li>Complete the application in short, guided steps — your answers are saved automatically as you type.</li>
                    <li>You can leave at any point and pick up right where you left off, right up until the deadline.</li>
                    <li>Review everything on the final step before submitting.</li>
                    <li>Once submitted, the application is locked and cannot be edited further.</li>
                </ol>
            </div>

            <!-- Application status -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($application->isSubmitted())
                    <div class="flex items-center gap-3 mb-4">
                        <span class="h-9 w-9 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Application Submitted</p>
                            <p class="text-xs text-gray-500">Submitted on {{ $application->submitted_at->format('d M Y, h:i A') }}. It is now read-only.</p>
                        </div>
                    </div>
                    <a href="{{ route('application.review') }}" class="inline-block px-5 py-2.5 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">
                        View Submitted Application
                    </a>
                @else
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-semibold text-gray-800">Application Progress</p>
                        <span class="text-xs text-gray-500">{{ $application->progressPercent() }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 mb-5">
                        <div class="bg-[#17458F] h-2 rounded-full transition-all" style="width: {{ $application->progressPercent() }}%"></div>
                    </div>

                    @if ($deadlinePassed)
                        <p class="text-sm text-red-600">The submission deadline has passed. You can no longer continue this application.</p>
                    @else
                        <a href="{{ route('application.step', min($application->current_step, $application->totalSteps())) }}"
                           class="inline-block px-5 py-2.5 text-sm font-semibold text-white bg-[#F7A81B] rounded-md hover:bg-[#d99311] transition">
                            {{ $application->current_step > 1 ? 'Continue Application' : 'Start Application' }}
                        </a>
                    @endif
                @endif
            </div>

            
        </div>
    </div>
</x-app-layout>
