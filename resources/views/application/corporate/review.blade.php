@php
    $stepNum = fn ($key) => \App\Services\ApplicationOptions::stepNumberForKey('corporate', $key);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-3xl font-bold leading-tight text-rotary-navy">Review &amp; Submit</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('application._progress', ['application' => $application, 'step' => $step])

            @if ($application->isSubmitted())
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    <span class="font-bold">Application ID: {{ $application->reference_number }}</span><br>
                    Submitted on {{ $application->submitted_at->format('d M Y, h:i A') }}. This application is now read-only.
                </div>
            @elseif ($locked)
                <div class="mb-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    The submission deadline has passed. This application can no longer be submitted.
                </div>
            @endif

            <div class="space-y-4">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800">Company Information</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('category')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <dl class="text-sm">
                        <div>
                            <dt class="text-xs text-gray-400">Company Size</dt>
                            <dd class="text-gray-700">{{ \App\Services\ApplicationOptions::COMPANY_SIZES[$application->company_size]['label'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800">Area of Focus</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('focus')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <p class="text-sm text-gray-700">{{ \App\Services\ApplicationOptions::FOCUS_AREAS[$application->focus_area] ?? '—' }}</p>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800">Corporate / Applicant Details</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('nomination')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                        @foreach ([
                            'Corporate / Foundation Name' => $application->corporate_foundation_name,
                            'CSR Registration Number' => $application->csr_registration_number,
                            'Industry / Sector' => $application->industry_sector,
                            'Registered / Head Office Address' => $application->head_office_location,
                            'Presence' => \App\Services\ApplicationOptions::CORPORATE_PRESENCE_OPTIONS[$application->corporate_presence] ?? null,
                            'Name of Business Group' => $application->business_group_name,
                            'Primary Contact — Name' => $application->primary_contact_name,
                            'Primary Contact — Designation' => $application->primary_contact_designation,
                            'Primary Contact — Email ID' => $application->primary_contact_email,
                            'Primary Contact — Mobile Number' => $application->primary_contact_mobile,
                            'Secondary Contact — Name' => $application->secondary_contact_name,
                            'Secondary Contact — Designation' => $application->secondary_contact_designation,
                            'Secondary Contact — Email ID' => $application->secondary_contact_email,
                            'Secondary Contact — Mobile Number' => $application->secondary_contact_mobile,
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs text-gray-400">{{ $label }}</dt>
                                <dd class="text-gray-700">{{ $value ?: '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800">Project Details</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('assessment')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <dl class="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                        @foreach ([
                            'Project Title' => $application->project_name,
                            'Project Launch Date' => $application->project_launch_date?->format('d M Y'),
                            'Project Completion Date / Continuing' => $application->project_completion_status === 'continuing' ? 'Continuing' : $application->project_completion_date?->format('d M Y'),
                            'Geographical Coverage' => $application->geographic_coverage,
                            'Execution Partners' => $application->implementation_partners,
                            'CSR Budget / Project Cost' => $application->csr_budget !== null ? '₹'.number_format((float) $application->csr_budget, 2) : null,
                            'Direct and Indirect Beneficiaries' => $application->beneficiaries_impacted,
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs text-gray-400">{{ $label }}</dt>
                                <dd class="text-gray-700">{{ $value ?: '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                    <dl class="mt-4 space-y-3 text-sm">
                        @foreach ([
                            'Brief Project Concept / Design' => $application->intervention_design,
                            'Unique Feature of the Initiative' => $application->unique_feature,
                            'Impact Assessment' => $application->outcomes_impact,
                        ] as $label => $value)
                            <div>
                                <dt class="text-xs text-gray-400">{{ $label }}</dt>
                                <dd class="whitespace-pre-line text-gray-700">{{ $value ? strip_tags($value) : '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Supporting Documents / Proof</p>
                        @forelse ($application->supportingDocuments as $document)
                            <a href="{{ route('application.supporting-documents.download', $document) }}" class="mt-2 block text-sm font-semibold text-[#17458F] hover:underline">
                                {{ $document->original_name }} ({{ strtoupper($document->media_type) }})
                            </a>
                        @empty
                            <p class="mt-2 text-sm text-gray-400">No supporting media uploaded.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            @unless ($application->isSubmitted())
                <form method="POST" action="{{ route('application.submit') }}" class="mt-6 rounded-lg bg-white p-6 shadow-sm"
                      onsubmit="return confirm('Once submitted, this application cannot be edited. Continue?');">
                    @csrf
                    <label class="mb-4 flex items-start gap-2.5 text-sm text-gray-600">
                        <input type="checkbox" required class="mt-0.5 rounded text-[#17458F] focus:ring-[#17458F]">
                        I confirm the information provided above is accurate and complete.
                    </label>

                    <button type="submit" {{ $locked ? 'disabled' : '' }}
                            class="w-full rounded-md bg-[#F7A81B] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#d99311] disabled:cursor-not-allowed disabled:opacity-50">
                        Submit Application
                    </button>
                </form>
            @endunless
        </div>
    </div>
</x-app-layout>
