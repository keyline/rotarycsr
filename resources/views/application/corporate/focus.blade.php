<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <p class="text-sm text-gray-500 mb-1">Select the primary Rotary area of focus that best matches your nominated CSR project.</p>

    <div class="space-y-2">
        @foreach (\App\Services\ApplicationOptions::FOCUS_AREAS as $key => $label)
            <label class="flex items-start gap-3 p-3.5 border rounded-lg cursor-pointer transition
                {{ $application->focus_area === $key ? 'border-[#17458F] bg-[#17458F]/5' : 'border-gray-200 hover:border-gray-300' }}">
                <input type="radio" name="focus_area" value="{{ $key }}" {{ $application->focus_area === $key ? 'checked' : '' }}
                       class="mt-0.5 text-[#17458F] focus:ring-[#17458F]" required>
                <span class="text-sm text-gray-700">{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <x-input-error :messages="$errors->get('focus_area')" class="mt-2" />
</x-application-step>
