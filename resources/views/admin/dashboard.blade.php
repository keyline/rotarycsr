<x-admin-layout :title="'Dashboard'">
    <!-- KPI cards -->
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
        @foreach ([
            ['label' => 'Total Applicants', 'value' => $stats['total'], 'color' => 'text-[#12325E]'],
            ['label' => 'Total Applications', 'value' => $applicationStats['total'], 'color' => 'text-[#12325E]'],
            ['label' => 'Corporate', 'value' => $stats['corporate'], 'color' => 'text-[#17458F]'],
            ['label' => 'Individual', 'value' => $stats['individual'], 'color' => 'text-[#17458F]'],
            ['label' => 'Verified', 'value' => $stats['verified'], 'color' => 'text-green-600'],
            ['label' => 'Pending Verification', 'value' => $stats['pending'], 'color' => 'text-amber-600'],
        ] as $card)
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <p class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-1 border-b border-gray-100 pb-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="font-display text-2xl font-bold text-rotary-navy">Application Status Overview</h2>
                <p class="mt-1 text-sm text-gray-500">Percentages are calculated from {{ $applicationStats['total'] }} total applications.</p>
            </div>
            <span class="mt-2 inline-flex w-fit rounded-full bg-[#17458F]/10 px-3 py-1 text-sm font-semibold text-[#17458F] sm:mt-0">
                {{ $applicationStats['total'] }} total
            </span>
        </div>

        <div class="mt-5 grid gap-6 xl:grid-cols-2">
            <div>
                <div class="mb-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">Approval Status</h3>
                    <p class="mt-1 text-xs text-gray-500">Approved, not approved, and submitted applications awaiting a decision.</p>
                </div>

                <div class="space-y-5">
                    @foreach ([
                        [
                            'label' => 'Approved',
                            'description' => 'Applications approved by the review team',
                            'count' => $applicationStats['approved'],
                            'percentage' => $applicationPercentages['approved'],
                            'bar' => 'bg-green-500',
                            'badge' => 'bg-green-50 text-green-700',
                        ],
                        [
                            'label' => 'Not Approved',
                            'description' => 'Rejected or blacklisted applications',
                            'count' => $applicationStats['not_approved'],
                            'percentage' => $applicationPercentages['not_approved'],
                            'bar' => 'bg-red-500',
                            'badge' => 'bg-red-50 text-red-700',
                        ],
                        [
                            'label' => 'Pending Review',
                            'description' => 'Submitted applications awaiting a decision',
                            'count' => $applicationStats['pending_review'],
                            'percentage' => $applicationPercentages['pending_review'],
                            'bar' => 'bg-amber-500',
                            'badge' => 'bg-amber-50 text-amber-700',
                        ],
                    ] as $status)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-gray-800">{{ $status['label'] }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $status['badge'] }}">{{ $status['count'] }}</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ $status['description'] }}</p>
                                </div>
                                <span class="shrink-0 text-lg font-bold text-gray-800">{{ number_format($status['percentage'], 1) }}%</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-gray-100" role="progressbar"
                                 aria-label="{{ $status['label'] }} applications"
                                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $status['percentage'] }}">
                                <div class="h-full rounded-full {{ $status['bar'] }}" style="width: {{ $status['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6 xl:border-l xl:border-t-0 xl:pl-6 xl:pt-0">
                <div class="mb-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-700">Application Progress</h3>
                    <p class="mt-1 text-xs text-gray-500">Submission progress across all started applications.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        [
                            'label' => 'Submitted',
                            'count' => $applicationStats['submitted'],
                            'percentage' => $applicationPercentages['submitted'],
                            'accent' => 'text-[#17458F]',
                            'bar' => 'bg-[#17458F]',
                        ],
                        [
                            'label' => 'In Progress',
                            'count' => $applicationStats['in_progress'],
                            'percentage' => $applicationPercentages['in_progress'],
                            'accent' => 'text-[#a4700f]',
                            'bar' => 'bg-[#F7A81B]',
                        ],
                    ] as $progress)
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ $progress['label'] }}</p>
                            <div class="mt-3 flex items-end justify-between gap-3">
                                <p class="text-3xl font-bold {{ $progress['accent'] }}">{{ $progress['count'] }}</p>
                                <p class="text-lg font-bold text-gray-800">{{ number_format($progress['percentage'], 1) }}%</p>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100" role="progressbar"
                                 aria-label="{{ $progress['label'] }} applications"
                                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress['percentage'] }}">
                                <div class="h-full rounded-full {{ $progress['bar'] }}" style="width: {{ $progress['percentage'] }}%"></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">of {{ $applicationStats['total'] }} total applications</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-admin-layout>
