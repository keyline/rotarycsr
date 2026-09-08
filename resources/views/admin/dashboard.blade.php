<x-admin-layout :title="'Dashboard'">
    <!-- KPI cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total Applicants', 'value' => $stats['total'], 'color' => 'text-[#12325E]'],
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
</x-admin-layout>
