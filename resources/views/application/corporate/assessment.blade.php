<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <section class="rounded-xl border border-amber-200 bg-amber-50 p-5">
        <h3 class="text-sm font-bold uppercase tracking-wide text-amber-900">Important Notes</h3>
        <p class="mt-2 text-sm font-semibold text-amber-900">The award will be evaluated on the basis of one specific CSR project.</p>
        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-amber-900">
            <li>The award will not be based on aggregate CSR expenditure incurred over a particular period.</li>
            <li>The applicant corporate may select any one project for submission.</li>
            <li>The selected project must have been initiated during the financial year ending 31 March 2026.</li>
            <li>The project may have been completed during that financial year or may continue into the following financial year.</li>
            <li>The project must have been executed in accordance with the applicable CSR regulations under the Companies Act, 2013.</li>
        </ul>
    </section>

    <section class="border-t border-gray-100 pt-6">
        <h3 class="font-display text-xl font-bold text-rotary-navy">Project Information</h3>
        <div class="mt-4 space-y-5">
            <div>
                <x-input-label for="project_name" value="Project Title" />
                <x-text-input id="project_name" name="project_name" type="text" class="block mt-1 w-full"
                              :value="old('project_name', $application->project_name)" required />
                <x-input-error :messages="$errors->get('project_name')" class="mt-1" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="project_launch_date" value="Project Launch Date" />
                    <x-text-input id="project_launch_date" name="project_launch_date" type="date" min="2025-04-01" max="2026-03-31" class="block mt-1 w-full"
                                  :value="old('project_launch_date', $application->project_launch_date?->format('Y-m-d'))" required />
                    <p class="mt-1 text-sm text-gray-500">Must fall between 1 April 2025 and 31 March 2026.</p>
                    <x-input-error :messages="$errors->get('project_launch_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="project_completion_status" value="Project Completion Date / Continuing" />
                    <select id="project_completion_status" name="project_completion_status" required
                            class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select status</option>
                        <option value="completed" @selected(old('project_completion_status', $application->project_completion_status) === 'completed')>Completed</option>
                        <option value="continuing" @selected(old('project_completion_status', $application->project_completion_status) === 'continuing')>Continuing</option>
                    </select>
                    <x-input-error :messages="$errors->get('project_completion_status')" class="mt-1" />
                </div>
            </div>

            <div id="completion-date-field">
                <x-input-label for="project_completion_date" value="Project Completion Date" />
                <x-text-input id="project_completion_date" name="project_completion_date" type="date" class="block mt-1 w-full"
                              :value="old('project_completion_date', $application->project_completion_date?->format('Y-m-d'))" />
                <p class="mt-1 text-sm text-gray-500">Required when the project status is Completed.</p>
                <x-input-error :messages="$errors->get('project_completion_date')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="geographic_coverage" value="Geographical Coverage of Project" />
                <x-text-input id="geographic_coverage" name="geographic_coverage" type="text" class="block mt-1 w-full"
                              placeholder="Village / Town / District / State"
                              :value="old('geographic_coverage', $application->geographic_coverage)" required />
                <x-input-error :messages="$errors->get('geographic_coverage')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="implementation_partners" value="Execution Partners (if any)" />
                <x-text-input id="implementation_partners" name="implementation_partners" type="text" class="block mt-1 w-full"
                              :value="old('implementation_partners', $application->implementation_partners)" />
                <x-input-error :messages="$errors->get('implementation_partners')" class="mt-1" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="csr_budget" value="CSR Budget / Project Cost (₹)" />
                    <x-text-input id="csr_budget" name="csr_budget" type="number" step="0.01" min="0" class="block mt-1 w-full"
                                  :value="old('csr_budget', $application->csr_budget)" required />
                    <x-input-error :messages="$errors->get('csr_budget')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="beneficiaries_impacted" value="Direct and Indirect Beneficiaries" />
                    <x-text-input id="beneficiaries_impacted" name="beneficiaries_impacted" type="text" class="block mt-1 w-full"
                                  :value="old('beneficiaries_impacted', $application->beneficiaries_impacted)" required />
                    <x-input-error :messages="$errors->get('beneficiaries_impacted')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="intervention_design" value="Brief Project Concept / Design" />
                <textarea id="intervention_design" name="intervention_design" rows="5" required
                          class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('intervention_design', strip_tags($application->intervention_design ?? '')) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Maximum 100 words.</p>
                <x-input-error :messages="$errors->get('intervention_design')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="unique_feature" value="Unique Feature of the Initiative, if any" />
                <textarea id="unique_feature" name="unique_feature" rows="5"
                          class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('unique_feature', strip_tags($application->unique_feature ?? '')) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Maximum 100 words.</p>
                <x-input-error :messages="$errors->get('unique_feature')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="outcomes_impact" value="Impact Assessment" />
                <textarea id="outcomes_impact" name="outcomes_impact" rows="6" required
                          class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('outcomes_impact', strip_tags($application->outcomes_impact ?? '')) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Maximum 150 words.</p>
                <x-input-error :messages="$errors->get('outcomes_impact')" class="mt-1" />
            </div>
        </div>
    </section>

    <section class="border-t border-gray-100 pt-6">
        <h3 class="font-display text-xl font-bold text-rotary-navy">Supporting Documents / Proof</h3>
        <p class="mt-1 text-sm leading-6 text-gray-500">Upload up to 10 project-related images or videos.</p>
        <input id="supporting_documents" name="supporting_documents[]" type="file" multiple
               accept=".jpg,.jpeg,.png,.webp,.mp4,.mov,.webm,image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm"
               class="mt-4 block w-full min-w-0 max-w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-[#17458F] file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-[#123669]" />
        <p class="mt-2 text-sm text-gray-500">Maximum size per image: 100 KB. Maximum size per video: 2 MB.</p>
        <x-input-error :messages="$errors->get('supporting_documents')" class="mt-2" />
        <x-input-error :messages="$errors->get('supporting_documents.*')" class="mt-2" />

        @if ($application->supportingDocuments->isNotEmpty())
            <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Already uploaded</p>
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach ($application->supportingDocuments as $document)
                        <li class="flex items-center justify-between gap-3">
                            <a href="{{ route('application.supporting-documents.download', $document) }}" class="min-w-0 truncate font-semibold text-[#17458F] hover:underline">
                                {{ $document->original_name }}
                            </a>
                            <span class="shrink-0 text-xs text-gray-400">{{ strtoupper($document->media_type) }} · {{ number_format($document->size / 1048576, 1) }} MB</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const status = document.getElementById('project_completion_status');
            const dateField = document.getElementById('completion-date-field');
            const dateInput = document.getElementById('project_completion_date');

            const updateCompletionDate = () => {
                const completed = status.value === 'completed';
                dateField.classList.toggle('hidden', !completed);
                dateInput.required = completed;
                if (!completed) dateInput.value = '';
            };

            status.addEventListener('change', updateCompletionDate, true);
            updateCompletionDate();
        });
    </script>
</x-application-step>
