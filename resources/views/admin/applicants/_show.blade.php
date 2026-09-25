@php
    $isCorporate = $applicant->applicant_type === 'corporate';
@endphp

<div class="flex items-start justify-between gap-4 mb-5 pb-5 border-b border-gray-100">
    <div>
        <h3 class="text-lg font-bold text-gray-800">{{ $applicant->name }}</h3>
        @if ($applicant->company_name)
            <p class="text-sm text-gray-500">{{ $applicant->company_name }}</p>
        @endif
        <p class="text-sm text-gray-500 mt-1">{{ $applicant->email }}</p>
    </div>
    <div class="text-right shrink-0">
        <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full {{ $isCorporate ? 'bg-[#17458F]/10 text-[#17458F]' : 'bg-[#F7A81B]/10 text-[#a4700f]' }}">
            {{ ucfirst($applicant->applicant_type ?? '—') }}
        </span>
        <p class="text-xs text-gray-400 mt-1.5">
            @if ($application?->isSubmitted())
                Submitted {{ $application->submitted_at->format('d M Y, h:i A') }}<br>
                <span class="font-semibold text-[#17458F]">{{ $application->reference_number }}</span>
            @elseif ($application)
                Draft — step {{ $application->current_step }} of {{ $application->totalSteps() }}
            @else
                Not started
            @endif
        </p>
    </div>
</div>

@if ($application?->isSubmitted())
    @php
        $reviewStatus = $application->review_status ?? 'pending';
        $reviewClasses = match ($reviewStatus) {
            'approved' => 'bg-green-50 text-green-700 border-green-200',
            'rejected' => 'bg-red-50 text-red-700 border-red-200',
            'blacklisted' => 'bg-gray-900 text-white border-gray-900',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };
    @endphp
    <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Review decision</p>
                <span class="mt-1.5 inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $reviewClasses }}">
                    {{ ucfirst($reviewStatus) }}
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'approved' => ['Approve', 'bg-green-600 hover:bg-green-700'],
                    'rejected' => ['Reject', 'bg-red-600 hover:bg-red-700'],
                    'blacklisted' => ['Blacklist', 'bg-gray-900 hover:bg-black'],
                ] as $decision => [$label, $classes])
                    <form method="POST" action="{{ route('admin.applications.decision', $application) }}"
                          @if ($decision === 'blacklisted') onsubmit="return confirm('Blacklist this applicant? They will be blocked from future award cycles.');" @endif>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="decision" value="{{ $decision }}">
                        <button type="submit"
                                class="rounded-lg px-3 py-2 text-xs font-bold text-white transition {{ $classes }} disabled:cursor-not-allowed disabled:opacity-40"
                                {{ $reviewStatus === $decision ? 'disabled' : '' }}>
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
        <p class="mt-3 text-xs text-gray-500">Saving a decision emails the applicant. Blacklisting also blocks all future applications for this email.</p>
    </div>
@endif

@if (! $application)
    <p class="text-sm text-gray-400 text-center py-8">This applicant hasn't started their application yet.</p>
@elseif ($isCorporate)
    <div class="space-y-4">
        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Company Information</h4>
            <dl class="grid gap-x-6 gap-y-2.5 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-gray-400">Company Size</dt>
                    <dd class="text-gray-700">{{ \App\Services\ApplicationOptions::COMPANY_SIZES[$application->company_size]['label'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Turnover — FY 2025–2026</dt>
                    <dd class="text-gray-700">{{ $application->company_turnover !== null ? '₹'.number_format((float) $application->company_turnover, 2).' Crore' : '—' }}</dd>
                </div>
            </dl>
        </div>

        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Area of Focus</h4>
            <p class="text-sm text-gray-700">{{ \App\Services\ApplicationOptions::FOCUS_AREAS[$application->focus_area] ?? '—' }}</p>
        </div>

        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Corporate / Applicant Details</h4>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
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

        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Project Details</h4>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
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
            <dl class="mt-3 space-y-2.5 text-sm">
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
            <div class="mt-4 border-t border-gray-100 pt-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Supporting Documents / Proof</p>
                @forelse ($application->supportingDocuments as $document)
                    <a href="{{ route('application.supporting-documents.download', $document) }}" class="mt-1.5 block text-sm font-semibold text-[#17458F] hover:underline">
                        {{ $document->original_name }} ({{ strtoupper($document->media_type) }})
                    </a>
                @empty
                    <p class="mt-1.5 text-sm text-gray-400">No supporting media uploaded.</p>
                @endforelse
            </div>
        </div>
    </div>
@else
    @php $projects = collect($application->ind_projects ?? [])->filter(fn ($project) => ! empty($project)); @endphp
    <div class="space-y-4">
        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Personal &amp; Professional Details</h4>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-2.5 text-sm">
                @foreach ([
                    'Designation' => $application->ind_designation,
                    'Organisation' => $application->ind_organisation,
                    'Industry' => $application->ind_industry,
                    'Location' => $application->ind_location,
                    'Years of CSR/ESG Experience' => $application->ind_csr_experience_years,
                    'Total Years of Experience' => $application->ind_total_experience_years,
                    'Annual CSR Budget Handled' => $application->ind_annual_budget_handled,
                    'Geographic Responsibility' => $application->ind_geographic_responsibility,
                ] as $label => $value)
                    <div>
                        <dt class="text-xs text-gray-400">{{ $label }}</dt>
                        <dd class="text-gray-700">{{ $value ?? '—' }}</dd>
                    </div>
                @endforeach
                <div class="sm:col-span-2">
                    <dt class="text-xs text-gray-400">Current CSR Responsibilities</dt>
                    <dd class="text-gray-700 [&_ul]:list-disc [&_ul]:pl-5">{!! $application->ind_current_responsibilities ?: '—' !!}</dd>
                </div>
            </dl>
        </div>

        @forelse ($projects as $index => $project)
            <div>
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">CSR Project {{ $index + 1 }}</h4>
                <dl class="space-y-2.5 text-sm">
                    @foreach ([
                        'Social problems identified and addressed' => $project['problem'] ?? null,
                        'CSR Intervention designed' => $project['intervention'] ?? null,
                        'CSR Investments used' => $project['investment'] ?? null,
                        'Beneficiaries impacted' => $project['beneficiaries'] ?? null,
                        'Outcomes / impact achieved' => $project['outcomes'] ?? null,
                    ] as $label => $value)
                        <div>
                            <dt class="text-xs text-gray-400">{{ $label }}</dt>
                            @if ($label === 'CSR Investments used')
                                <dd class="text-gray-700">{{ $value ?: '—' }}</dd>
                            @else
                                <dd class="text-gray-700 [&_ul]:list-disc [&_ul]:pl-5">{!! $value ?: '—' !!}</dd>
                            @endif
                        </div>
                    @endforeach
                </dl>
            </div>
        @empty
            <p class="text-sm text-gray-400">No CSR projects added yet.</p>
        @endforelse
    </div>
@endif
