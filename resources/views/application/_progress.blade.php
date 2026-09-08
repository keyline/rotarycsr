@php
    $steps = \App\Services\ApplicationOptions::steps($application->applicant_type);
@endphp
<div class="flex items-center gap-2 mb-8">
    @foreach ($steps as $i => $key)
        @php $num = $i + 1; @endphp
        <div class="flex items-center gap-2 flex-1 last:flex-none">
            <div class="flex flex-col items-center gap-1">
                <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-semibold shrink-0
                    {{ $num < $step ? 'bg-[#17458F] text-white' : ($num === $step ? 'bg-[#F7A81B] text-white' : 'bg-gray-100 text-gray-400') }}">
                    @if ($num < $step)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @else
                        {{ $num }}
                    @endif
                </div>
                <span class="text-[10px] text-gray-500 hidden sm:block text-center max-w-[80px] leading-tight">{{ \App\Services\ApplicationOptions::stepTitle($application->applicant_type, $key) }}</span>
            </div>
            @if (! $loop->last)
                <div class="h-0.5 flex-1 {{ $num < $step ? 'bg-[#17458F]' : 'bg-gray-100' }}"></div>
            @endif
        </div>
    @endforeach
</div>
