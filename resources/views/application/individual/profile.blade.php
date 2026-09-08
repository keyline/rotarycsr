<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <div>
        <x-input-label value="Name" />
        <div class="mt-1 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-md text-gray-600">{{ $application->user->name }}</div>
        <p class="mt-1 text-xs text-gray-400">From your account details. To change it, update your <a href="{{ route('profile.edit') }}" class="underline">profile</a>.</p>
    </div>

    <div>
        <x-input-label for="ind_designation" value="Designation" />
        <x-text-input id="ind_designation" name="ind_designation" type="text" class="block mt-1 w-full" :value="$application->ind_designation" required />
        <x-input-error :messages="$errors->get('ind_designation')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="ind_organisation" value="Organisation" />
        <x-text-input id="ind_organisation" name="ind_organisation" type="text" class="block mt-1 w-full" :value="$application->ind_organisation" required />
        <x-input-error :messages="$errors->get('ind_organisation')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="ind_industry" value="Industry" />
        <x-text-input id="ind_industry" name="ind_industry" type="text" class="block mt-1 w-full" :value="$application->ind_industry" required />
        <x-input-error :messages="$errors->get('ind_industry')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="ind_location" value="Location" />
        <x-text-input id="ind_location" name="ind_location" type="text" class="block mt-1 w-full" :value="$application->ind_location" required />
        <x-input-error :messages="$errors->get('ind_location')" class="mt-1" />
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="ind_csr_experience_years" value="Years of CSR/ESG Experience" />
            <x-text-input id="ind_csr_experience_years" name="ind_csr_experience_years" type="number" min="0" max="80" class="block mt-1 w-full" :value="$application->ind_csr_experience_years" required />
            <x-input-error :messages="$errors->get('ind_csr_experience_years')" class="mt-1" />
        </div>
        <div>
            <x-input-label for="ind_total_experience_years" value="Total Years of Professional Experience" />
            <x-text-input id="ind_total_experience_years" name="ind_total_experience_years" type="number" min="0" max="80" class="block mt-1 w-full" :value="$application->ind_total_experience_years" required />
            <x-input-error :messages="$errors->get('ind_total_experience_years')" class="mt-1" />
        </div>
    </div>

    <div>
        <x-input-label for="ind_current_responsibilities" value="Current CSR Responsibilities" />
        <x-rich-textarea name="ind_current_responsibilities" :value="old('ind_current_responsibilities', $application->ind_current_responsibilities)" rows="3" class="mt-1" />
        <x-input-error :messages="$errors->get('ind_current_responsibilities')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="ind_annual_budget_handled" value="Approximate Annual CSR Budget Handled (Last 3 Years)" />
        <x-text-input id="ind_annual_budget_handled" name="ind_annual_budget_handled" type="text" class="block mt-1 w-full" :value="$application->ind_annual_budget_handled" required />
        <x-input-error :messages="$errors->get('ind_annual_budget_handled')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="ind_geographic_responsibility" value="Geographic Responsibility" />
        <x-text-input id="ind_geographic_responsibility" name="ind_geographic_responsibility" type="text" class="block mt-1 w-full" :value="$application->ind_geographic_responsibility" required />
        <x-input-error :messages="$errors->get('ind_geographic_responsibility')" class="mt-1" />
    </div>
</x-application-step>
