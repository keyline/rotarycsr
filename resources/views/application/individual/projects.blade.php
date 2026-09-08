@php
    $hasProject2 = ! empty(array_filter($application->ind_projects[1] ?? []));
    $hasProject3 = ! empty(array_filter($application->ind_projects[2] ?? []));
@endphp

<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <div x-data="{ showProject2: {{ $hasProject2 ? 'true' : 'false' }}, showProject3: {{ $hasProject3 ? 'true' : 'false' }} }">
        <p class="text-sm text-gray-500 mb-1">
            Tell us about up to 3 of your most impactful CSR projects from the last 3 years. The first is required — add more if you have them.
        </p>

        <div class="mt-4">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">CSR Project 1</h3>
            @include('application.individual._project-fields-flat', ['index' => 1])
        </div>

        <div x-show="!showProject2" class="mt-6">
            <button type="button" @click="showProject2 = true"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#17458F] hover:text-[#123669]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Another Project
            </button>
        </div>

        <div x-show="showProject2" x-cloak id="project-2-block" class="mt-6 pt-6 border-t border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-800">CSR Project 2 <span class="text-xs font-normal text-gray-400">(optional)</span></h3>
                <button type="button"
                        @click="
                            showProject2 = false;
                            document.querySelectorAll('#project-2-block textarea, #project-2-block input[type=text], #project-2-block [data-rich-editor-input]').forEach(el => {
                                if (el.hasAttribute('contenteditable')) { el.innerHTML = ''; } else { el.value = ''; }
                            });
                            $el.closest('form').dispatchEvent(new Event('input', { bubbles: true }));
                        "
                        class="text-xs font-semibold text-red-500 hover:text-red-700">Remove</button>
            </div>
            @include('application.individual._project-fields-flat', ['index' => 2])
        </div>

        <div x-show="showProject2 && !showProject3" class="mt-6">
            <button type="button" @click="showProject3 = true"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#17458F] hover:text-[#123669]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add a Third Project
            </button>
        </div>

        <div x-show="showProject3" x-cloak id="project-3-block" class="mt-6 pt-6 border-t border-gray-100">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-800">CSR Project 3 <span class="text-xs font-normal text-gray-400">(optional)</span></h3>
                <button type="button"
                        @click="
                            showProject3 = false;
                            document.querySelectorAll('#project-3-block textarea, #project-3-block input[type=text], #project-3-block [data-rich-editor-input]').forEach(el => {
                                if (el.hasAttribute('contenteditable')) { el.innerHTML = ''; } else { el.value = ''; }
                            });
                            $el.closest('form').dispatchEvent(new Event('input', { bubbles: true }));
                        "
                        class="text-xs font-semibold text-red-500 hover:text-red-700">Remove</button>
            </div>
            @include('application.individual._project-fields-flat', ['index' => 3])
        </div>
    </div>
</x-application-step>
