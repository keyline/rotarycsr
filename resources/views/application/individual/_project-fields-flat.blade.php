@php
    $project = $application->ind_projects[$index - 1] ?? [];
@endphp

<div class="grid gap-4">
    <div>
        <x-input-label :for="'problem_'.$index" value="Social problems identified and addressed" />
        <x-rich-textarea :name="'problem_'.$index" :value="old('problem_'.$index, $project['problem'] ?? '')" rows="3" class="mt-1" />
        <x-input-error :messages="$errors->get('problem_'.$index)" class="mt-1" />
    </div>

    <div>
        <x-input-label :for="'intervention_'.$index" value="CSR Intervention designed" />
        <x-rich-textarea :name="'intervention_'.$index" :value="old('intervention_'.$index, $project['intervention'] ?? '')" rows="3" class="mt-1" />
        <x-input-error :messages="$errors->get('intervention_'.$index)" class="mt-1" />
    </div>

    <div>
        <x-input-label :for="'investment_'.$index" value="CSR Investments used" />
        <x-text-input :id="'investment_'.$index" :name="'investment_'.$index" type="text" class="block mt-1 w-full" :value="$project['investment'] ?? ''" />
        <x-input-error :messages="$errors->get('investment_'.$index)" class="mt-1" />
    </div>

    <div>
        <x-input-label :for="'beneficiaries_'.$index" value="Number of Direct / Indirect Beneficiaries impacted" />
        <x-rich-textarea :name="'beneficiaries_'.$index" :value="old('beneficiaries_'.$index, $project['beneficiaries'] ?? '')" rows="2" class="mt-1" />
        <x-input-error :messages="$errors->get('beneficiaries_'.$index)" class="mt-1" />
    </div>

    <div>
        <x-input-label :for="'outcomes_'.$index" value="Outcomes / Impact Achieved" />
        <x-rich-textarea :name="'outcomes_'.$index" :value="old('outcomes_'.$index, $project['outcomes'] ?? '')" rows="3" class="mt-1" />
        <x-input-error :messages="$errors->get('outcomes_'.$index)" class="mt-1" />
    </div>
</div>
