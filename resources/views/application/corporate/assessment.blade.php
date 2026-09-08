<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <div>
        <x-input-label for="problem_addressed" value="What problem was addressed?" />
        <x-rich-textarea name="problem_addressed" :value="old('problem_addressed', $application->problem_addressed)" rows="3" class="mt-1" />
        <x-input-error :messages="$errors->get('problem_addressed')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="intervention_design" value="What was the CSR intervention / project design?" />
        <x-rich-textarea name="intervention_design" :value="old('intervention_design', $application->intervention_design)" rows="3" class="mt-1" />
        <x-input-error :messages="$errors->get('intervention_design')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="beneficiaries_impacted" value="How many direct and indirect beneficiaries were impacted?" />
        <x-rich-textarea name="beneficiaries_impacted" :value="old('beneficiaries_impacted', $application->beneficiaries_impacted)" rows="3" class="mt-1" />
        <x-input-error :messages="$errors->get('beneficiaries_impacted')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="outcomes_impact" value="What outcomes and impact were achieved?" />
        <x-rich-textarea name="outcomes_impact" :value="old('outcomes_impact', $application->outcomes_impact)" rows="3" class="mt-1" />
        <x-input-error :messages="$errors->get('outcomes_impact')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="implementation_partners" value="List major implementation / community partners" />
        <x-rich-textarea name="implementation_partners" :value="old('implementation_partners', $application->implementation_partners)" rows="2" class="mt-1" />
        <x-input-error :messages="$errors->get('implementation_partners')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="additional_info" value="Any other information you would like to add" />
        <x-rich-textarea name="additional_info" :value="old('additional_info', $application->additional_info)" rows="2" class="mt-1" />
        <x-input-error :messages="$errors->get('additional_info')" class="mt-1" />
    </div>
</x-application-step>
