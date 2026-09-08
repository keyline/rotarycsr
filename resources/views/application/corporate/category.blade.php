<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <p class="text-sm text-gray-500 mb-1">Choose the category that matches your organisation's average annual CSR expenditure.</p>

    <div class="space-y-3">
        @foreach (\App\Services\ApplicationOptions::CORPORATE_CATEGORIES as $key => $option)
            <label class="flex items-start gap-3 p-4 border rounded-lg cursor-pointer transition
                {{ $application->corporate_category === $key ? 'border-[#17458F] bg-[#17458F]/5' : 'border-gray-200 hover:border-gray-300' }}">
                <input type="radio" name="corporate_category" value="{{ $key }}" {{ $application->corporate_category === $key ? 'checked' : '' }}
                       class="mt-1 text-[#17458F] focus:ring-[#17458F]" required>
                <span>
                    <span class="block text-sm font-semibold text-gray-800">{{ $option['label'] }}</span>
                    <span class="block text-xs text-gray-500 mt-0.5">{{ $option['help'] }}</span>
                </span>
            </label>
        @endforeach
    </div>

    <x-input-error :messages="$errors->get('corporate_category')" class="mt-2" />
</x-application-step>
