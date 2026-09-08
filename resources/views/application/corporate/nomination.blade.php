<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <div>
        <x-input-label for="corporate_foundation_name" value="Corporate / Foundation Name" />
        <x-text-input id="corporate_foundation_name" name="corporate_foundation_name" type="text" class="block mt-1 w-full"
                      :value="$application->corporate_foundation_name" required />
        <x-input-error :messages="$errors->get('corporate_foundation_name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="csr_registration_number" value="CSR Registration Number (Corporate Foundation)" />
        <x-text-input id="csr_registration_number" name="csr_registration_number" type="text" class="block mt-1 w-full"
                      :value="$application->csr_registration_number" required />
        <x-input-error :messages="$errors->get('csr_registration_number')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="industry_sector" value="Industry / Sector" />
        <x-text-input id="industry_sector" name="industry_sector" type="text" class="block mt-1 w-full"
                      :value="$application->industry_sector" required />
        <x-input-error :messages="$errors->get('industry_sector')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="head_office_location" value="Head Office / Project Location" />
        <x-text-input id="head_office_location" name="head_office_location" type="text" class="block mt-1 w-full"
                      :value="$application->head_office_location" required />
        <x-input-error :messages="$errors->get('head_office_location')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="project_name" value="Name of CSR Project / Initiative" />
        <x-text-input id="project_name" name="project_name" type="text" class="block mt-1 w-full"
                      :value="$application->project_name" required />
        <x-input-error :messages="$errors->get('project_name')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="project_period" value="Project Period / Duration" />
        <x-text-input id="project_period" name="project_period" type="text" class="block mt-1 w-full" placeholder="e.g. April 2025 – March 2026"
                      :value="$application->project_period" required />
        <p class="mt-1 text-xs text-gray-500">Must have started / been implemented during FY 2025-26.</p>
        <x-input-error :messages="$errors->get('project_period')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="geographic_coverage" value="Geographic Coverage" />
        <x-text-input id="geographic_coverage" name="geographic_coverage" type="text" class="block mt-1 w-full"
                      :value="$application->geographic_coverage" required />
        <x-input-error :messages="$errors->get('geographic_coverage')" class="mt-1" />
    </div>

    <div>
        <x-input-label for="csr_budget" value="CSR Budget / Investment for the Nominated CSR Project (₹)" />
        <x-text-input id="csr_budget" name="csr_budget" type="number" step="0.01" min="0" class="block mt-1 w-full"
                      :value="$application->csr_budget" required />
        <x-input-error :messages="$errors->get('csr_budget')" class="mt-1" />
    </div>
</x-application-step>
