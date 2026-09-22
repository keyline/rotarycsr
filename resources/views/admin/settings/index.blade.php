<x-admin-layout :title="'Settings'">
    <div class="max-w-4xl">
        <!-- Deadline -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 h-fit">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Global Submission Deadline</h2>
            <p class="text-xs text-gray-500 mb-4">
                Once this passes, all applicants are locked out of editing or submitting.
            </p>

            @if ($deadline)
                <p class="text-sm mb-4 px-3 py-2 rounded-md {{ \Illuminate\Support\Carbon::parse($deadline)->isPast() ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-[#17458F]/5 text-[#17458F] border border-[#17458F]/20' }}">
                    {{ \Illuminate\Support\Carbon::parse($deadline)->isPast() ? 'Closed since' : 'Closes' }}
                    {{ \Illuminate\Support\Carbon::parse($deadline)->format('d M Y, h:i A') }}
                </p>
            @endif

            <form method="POST" action="{{ route('admin.settings.deadline') }}" class="space-y-3">
                @csrf
                @method('PUT')
                <input type="datetime-local" name="submission_deadline"
                       value="{{ $deadline ? \Illuminate\Support\Carbon::parse($deadline)->format('Y-m-d\TH:i') : '' }}"
                       class="w-full text-sm border-gray-300 rounded-md focus:border-[#17458F] focus:ring-[#17458F]" required>
                @error('submission_deadline')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="w-full px-3 py-2 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">
                    Save Deadline
                </button>
            </form>
        </div>
    </div>

    <div class="mt-6 max-w-4xl rounded-lg border border-gray-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-gray-800">Application Decision Emails</h2>
        <p class="mt-1 text-xs leading-5 text-gray-500">
            These emails are sent when an application is approved, rejected, or blacklisted.
            Available placeholders: <code>{name}</code>, <code>{decision}</code>, and <code>{application_type}</code>.
            HTML is supported in the message body.
        </p>

        <form method="POST" action="{{ route('admin.settings.decision-emails') }}" class="mt-5 space-y-6">
            @csrf
            @method('PUT')

            @foreach (['approved' => 'Approved', 'rejected' => 'Rejected', 'blacklisted' => 'Blacklisted'] as $decision => $label)
                <fieldset class="rounded-lg border border-gray-200 p-4">
                    <legend class="px-2 text-xs font-bold uppercase tracking-wider text-[#17458F]">{{ $label }} email</legend>
                    <div class="space-y-3">
                        <div>
                            <label for="{{ $decision }}-subject" class="mb-1 block text-xs font-medium text-gray-600">Subject</label>
                            <input id="{{ $decision }}-subject" type="text" name="templates[{{ $decision }}][subject]"
                                   value="{{ old("templates.{$decision}.subject", $decisionMailTemplates[$decision]['subject']) }}"
                                   class="w-full rounded-md border-gray-300 text-sm focus:border-[#17458F] focus:ring-[#17458F]" required>
                            @error("templates.{$decision}.subject")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="{{ $decision }}-body" class="mb-1 block text-xs font-medium text-gray-600">Message body</label>
                            <x-rich-textarea
                                :name="'templates['.$decision.'][body]'"
                                :id="$decision.'-body'"
                                :value="old('templates.'.$decision.'.body', $decisionMailTemplates[$decision]['body'])"
                                rows="6"
                            />
                            @error("templates.{$decision}.body")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>
            @endforeach

            <button type="submit" class="rounded-md bg-[#17458F] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#123669]">
                Save Email Templates
            </button>
        </form>
    </div>
</x-admin-layout>
