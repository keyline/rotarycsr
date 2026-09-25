<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <p class="mb-1 text-sm text-gray-500">Provide your company size and turnover for FY 2025–2026.</p>

    <div class="space-y-3">
        <x-input-label value="Company Size" />
        @foreach (\App\Services\ApplicationOptions::COMPANY_SIZES as $key => $option)
            <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer transition
                {{ $application->company_size === $key ? 'border-[#17458F] bg-[#17458F]/5' : 'border-gray-200 hover:border-gray-300' }}">
                <input type="radio" name="company_size" value="{{ $key }}" {{ $application->company_size === $key ? 'checked' : '' }}
                       class="mt-1 text-[#17458F] focus:ring-[#17458F]" required>
                <span>
                    <span class="block text-sm font-semibold text-gray-800">{{ $option['label'] }}</span>
                    <span class="block text-xs text-gray-500 mt-0.5">{{ $option['help'] }}</span>
                </span>
            </label>
        @endforeach
    </div>

    <x-input-error :messages="$errors->get('company_size')" class="mt-2" />

    <div>
        <x-input-label for="company_turnover" value="Turnover — FY 2025–2026 (₹ Crore)" />
        <x-text-input id="company_turnover" name="company_turnover" type="number" step="0.01" min="0" max="9999999999999.99"
                      class="mt-1 block w-full" :value="$application->company_turnover" required />
        <x-input-error :messages="$errors->get('company_turnover')" class="mt-1" />
    </div>
</x-application-step>
