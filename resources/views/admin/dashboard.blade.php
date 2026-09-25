<x-admin-layout :title="'Dashboard'">
    <div class="space-y-4">
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6" aria-label="Dashboard totals">
            @foreach ([
                ['label' => 'Total Applicants', 'value' => $stats['total'], 'color' => 'text-[#12325E]'],
                ['label' => 'Total Applications', 'value' => $applicationStats['total'], 'color' => 'text-[#12325E]'],
                ['label' => 'Corporate', 'value' => $stats['corporate'], 'color' => 'text-[#17458F]'],
                ['label' => 'Individual', 'value' => $stats['individual'], 'color' => 'text-[#17458F]'],
                ['label' => 'Verified', 'value' => $stats['verified'], 'color' => 'text-green-600'],
                ['label' => 'Pending Verification', 'value' => $stats['pending'], 'color' => 'text-amber-600'],
            ] as $card)
                <div class="min-w-0 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <p class="text-xs font-medium text-gray-500">{{ $card['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold leading-tight {{ $card['color'] }}">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </section>

        <div class="grid gap-4 xl:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="approval-status-heading">
                <div class="border-b border-gray-100 pb-3">
                    <h2 id="approval-status-heading" class="text-base font-bold text-rotary-navy">Approval Status</h2>
                    <p class="mt-1 text-xs text-gray-500">Of {{ $applicationStats['total'] }} total applications</p>
                </div>

                <div class="mt-4 space-y-4">
                    @foreach ([
                        ['label' => 'Approved', 'count' => $applicationStats['approved'], 'percentage' => $applicationPercentages['approved'], 'bar' => 'bg-green-500', 'countColor' => 'text-green-700'],
                        ['label' => 'Not Approved', 'count' => $applicationStats['not_approved'], 'percentage' => $applicationPercentages['not_approved'], 'bar' => 'bg-red-500', 'countColor' => 'text-red-700'],
                        ['label' => 'Pending Review', 'count' => $applicationStats['pending_review'], 'percentage' => $applicationPercentages['pending_review'], 'bar' => 'bg-amber-500', 'countColor' => 'text-amber-700'],
                    ] as $status)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-gray-700">{{ $status['label'] }}</span>
                                <span class="shrink-0 font-semibold text-gray-700">
                                    <span class="{{ $status['countColor'] }}">{{ $status['count'] }}</span>
                                    <span class="ml-2 text-xs font-medium text-gray-500">{{ number_format($status['percentage'], 1) }}%</span>
                                </span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100" role="progressbar"
                                 aria-label="{{ $status['label'] }} applications"
                                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $status['percentage'] }}">
                                <div class="h-full rounded-full {{ $status['bar'] }}" style="width: {{ $status['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="application-progress-heading">
                <div class="border-b border-gray-100 pb-3">
                    <h2 id="application-progress-heading" class="text-base font-bold text-rotary-navy">Application Progress</h2>
                    <p class="mt-1 text-xs text-gray-500">Of {{ $applicationStats['total'] }} started applications</p>
                </div>

                <div class="mt-4 space-y-4">
                    @foreach ([
                        ['label' => 'Submitted', 'count' => $applicationStats['submitted'], 'percentage' => $applicationPercentages['submitted'], 'accent' => 'text-[#17458F]', 'bar' => 'bg-[#17458F]'],
                        ['label' => 'In Progress', 'count' => $applicationStats['in_progress'], 'percentage' => $applicationPercentages['in_progress'], 'accent' => 'text-[#a4700f]', 'bar' => 'bg-[#F7A81B]'],
                    ] as $progress)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-gray-700">{{ $progress['label'] }}</span>
                                <span class="shrink-0 font-semibold text-gray-700">
                                    <span class="{{ $progress['accent'] }}">{{ $progress['count'] }}</span>
                                    <span class="ml-2 text-xs font-medium text-gray-500">{{ number_format($progress['percentage'], 1) }}%</span>
                                </span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100" role="progressbar"
                                 aria-label="{{ $progress['label'] }} applications"
                                 aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress['percentage'] }}">
                                <div class="h-full rounded-full {{ $progress['bar'] }}" style="width: {{ $progress['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
