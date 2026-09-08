@php
    $stepNum = fn ($key) => \App\Services\ApplicationOptions::stepNumberForKey('corporate', $key);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Review &amp; Submit</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('application._progress', ['application' => $application, 'step' => $step])

            @if ($application->isSubmitted())
                <div class="mb-6 px-4 py-3 text-sm bg-green-50 text-green-700 border border-green-200 rounded-md">
                    Submitted on {{ $application->submitted_at->format('d M Y, h:i A') }}. This application is now read-only.
                </div>
            @elseif ($locked)
                <div class="mb-6 px-4 py-3 text-sm bg-amber-50 text-amber-800 border border-amber-200 rounded-md">
                    The submission deadline has passed. This application can no longer be submitted.
                </div>
            @endif

            <div class="space-y-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">Corporate Category</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('category')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <p class="text-sm text-gray-600">{{ \App\Services\ApplicationOptions::CORPORATE_CATEGORIES[$application->corporate_category]['label'] ?? '—' }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">Primary Rotary Area of Focus</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('focus')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <p class="text-sm text-gray-600">{{ \App\Services\ApplicationOptions::FOCUS_AREAS[$application->focus_area] ?? '—' }}</p>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">CSR Project Nomination</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('nomination')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        @foreach ([
                            'Corporate / Foundation Name' => $application->corporate_foundation_name,
                            'CSR Registration Number' => $application->csr_registration_number,
                            'Industry / Sector' => $application->industry_sector,
                            'Head Office / Project Location' => $application->head_office_location,
                            'Project Name' => $application->project_name,
                            'Project Period' => $application->project_period,
                            'Geographic Coverage' => $application->geographic_coverage,
                            'CSR Budget' => $application->csr_budget !== null ? '₹'.number_format((float) $application->csr_budget, 2) : null,
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs text-gray-400">{{ $label }}</dt>
                                <dd class="text-gray-700">{{ $value ?: '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-800">Project Assessment</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('assessment')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <dl class="space-y-3 text-sm">
                        @foreach ([
                            'What problem was addressed?' => $application->problem_addressed,
                            'CSR intervention / project design' => $application->intervention_design,
                            'Beneficiaries impacted' => $application->beneficiaries_impacted,
                            'Outcomes / impact achieved' => $application->outcomes_impact,
                            'Implementation / community partners' => $application->implementation_partners,
                            'Additional information' => $application->additional_info,
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs text-gray-400">{{ $label }}</dt>
                                <dd class="text-gray-700 [&_ul]:list-disc [&_ul]:pl-5">{!! $value ?: '—' !!}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>

            @unless ($application->isSubmitted())
                <form method="POST" action="{{ route('application.submit') }}" class="mt-6 bg-white shadow-sm sm:rounded-lg p-6"
                      onsubmit="return confirm('Once submitted, this application cannot be edited. Continue?');">
                    @csrf
                    <label class="flex items-start gap-2.5 text-sm text-gray-600 mb-4">
                        <input type="checkbox" required class="mt-0.5 rounded text-[#17458F] focus:ring-[#17458F]">
                        I confirm the information provided above is accurate and complete.
                    </label>

                    <button type="submit" {{ $locked ? 'disabled' : '' }}
                            class="w-full px-5 py-3 text-sm font-semibold text-white bg-[#F7A81B] rounded-md hover:bg-[#d99311] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Submit Application
                    </button>
                </form>
            @endunless
        </div>
    </div>
</x-app-layout>
