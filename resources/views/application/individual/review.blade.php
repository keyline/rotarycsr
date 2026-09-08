@php
    $stepNum = fn ($key) => \App\Services\ApplicationOptions::stepNumberForKey('individual', $key);
    $projects = collect($application->ind_projects ?? [])->filter(fn ($p) => ! empty($p));
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
                        <h3 class="text-sm font-semibold text-gray-800">Personal &amp; Professional Details</h3>
                        @unless ($application->isSubmitted())
                            <a href="{{ route('application.step', $stepNum('profile')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                        @endunless
                    </div>
                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        @foreach ([
                            'Name' => $application->user->name,
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

                @forelse ($projects as $i => $project)
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-gray-800">CSR Project {{ $i + 1 }}</h3>
                            @unless ($application->isSubmitted())
                                <a href="{{ route('application.step', $stepNum('projects')) }}" class="text-xs font-semibold text-[#17458F] hover:underline">Edit</a>
                            @endunless
                        </div>
                        <dl class="space-y-3 text-sm">
                            <div>
                                <dt class="text-xs text-gray-400">Social problems identified and addressed</dt>
                                <dd class="text-gray-700 [&_ul]:list-disc [&_ul]:pl-5">{!! $project['problem'] ?? '—' !!}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400">CSR Intervention designed</dt>
                                <dd class="text-gray-700 [&_ul]:list-disc [&_ul]:pl-5">{!! $project['intervention'] ?? '—' !!}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400">CSR Investments used</dt>
                                <dd class="text-gray-700">{{ $project['investment'] ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400">Number of Direct / Indirect Beneficiaries impacted</dt>
                                <dd class="text-gray-700 [&_ul]:list-disc [&_ul]:pl-5">{!! $project['beneficiaries'] ?? '—' !!}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400">Outcomes / Impact Achieved</dt>
                                <dd class="text-gray-700 [&_ul]:list-disc [&_ul]:pl-5">{!! $project['outcomes'] ?? '—' !!}</dd>
                            </div>
                        </dl>
                    </div>
                @empty
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-400">
                        No CSR projects added yet — at least one is required before you can submit.
                    </div>
                @endforelse
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
