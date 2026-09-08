@props(['application', 'step', 'totalSteps', 'stepKey', 'locked' => false])

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ \App\Services\ApplicationOptions::stepTitle($application->applicant_type, $stepKey) }}
            </h2>
            <span class="text-sm text-gray-500">Step {{ $step }} of {{ $totalSteps }}</span>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('application._progress', ['application' => $application, 'step' => $step])

            @if ($locked)
                <div class="mb-6 px-4 py-3 text-sm bg-amber-50 text-amber-800 border border-amber-200 rounded-md">
                    This application is locked and can no longer be edited.
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 sm:p-8">
                <form id="wizard-form" method="POST" action="{{ route('application.step', $step) }}">
                    @csrf

                    <div class="space-y-5 {{ $locked ? 'opacity-60 pointer-events-none' : '' }}">
                        {{ $slot }}
                    </div>

                    <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                        <div class="flex items-center gap-1.5 text-xs text-gray-400" id="autosave-status">&nbsp;</div>

                        <div class="flex items-center gap-3">
                            @if ($step > 1)
                                <a href="{{ route('application.step', $step - 1) }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Back</a>
                            @endif

                            @unless ($locked)
                                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">
                                    Save &amp; Continue
                                </button>
                            @endunless
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Autosave toast -->
    <div id="autosave-toast" class="autosave-toast fixed top-20 right-5 z-50 flex items-center gap-2 px-3.5 py-2 bg-[#17458F] text-white text-xs font-semibold rounded-full shadow-lg pointer-events-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
        </svg>
        Saved
    </div>

    @unless ($locked)
        @include('application._autosave', ['stepKey' => $stepKey])
    @endunless
</x-app-layout>
