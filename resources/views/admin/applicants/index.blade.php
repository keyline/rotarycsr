<x-admin-layout :title="'Applicants'">
    <div x-data="{
        open: false,
        loading: false,
        content: '',
        emailOpen: false,
        emailAction: '',
        emailType: 'general',
        emailRecipientName: '',
        emailRecipientEmail: '',
        emailSubject: '',
        emailMessage: '',
        emailMarkWinner: false,
        selected: [],
        visibleIds: @js($applicants->pluck('id')->values()),
        allSelected() {
            return this.visibleIds.length > 0 && this.visibleIds.every(id => this.selected.includes(id));
        },
        toggleAll(checked) {
            this.selected = checked ? [...this.visibleIds] : [];
        },
        composeEmail(applicant, type) {
            this.emailAction = applicant.action;
            this.emailType = type;
            this.emailRecipientName = applicant.name;
            this.emailRecipientEmail = applicant.email;
            this.emailSubject = type === 'award' ? @js(\App\Services\ApplicantMessageMailer::AWARD_SUBJECT) : '';
            this.emailMessage = type === 'award' ? @js(\App\Services\ApplicantMessageMailer::AWARD_MESSAGE) : @js(\App\Services\ApplicantMessageMailer::GENERAL_MESSAGE);
            this.emailMarkWinner = false;
            this.emailOpen = true;
        }
    }" @keydown.escape.window="open = false; emailOpen = false">
        <!-- Filters -->
        <form method="GET" class="bg-white border border-gray-200 rounded-lg p-4 mb-4 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email, or company"
                       class="w-full text-sm border-gray-300 rounded-md focus:border-[#17458F] focus:ring-[#17458F]">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <select name="applicant_type" class="text-sm border-gray-300 rounded-md focus:border-[#17458F] focus:ring-[#17458F]">
                    <option value="">All</option>
                    <option value="corporate" {{ ($filters['applicant_type'] ?? '') === 'corporate' ? 'selected' : '' }}>Corporate</option>
                    <option value="individual" {{ ($filters['applicant_type'] ?? '') === 'individual' ? 'selected' : '' }}>Individual</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="text-sm border-gray-300 rounded-md focus:border-[#17458F] focus:ring-[#17458F]">
                    <option value="">All</option>
                    <option value="verified" {{ ($filters['status'] ?? '') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Review</label>
                <select name="review_status" class="text-sm border-gray-300 rounded-md focus:border-[#17458F] focus:ring-[#17458F]">
                    <option value="">All</option>
                    @foreach (['pending', 'approved', 'rejected', 'blacklisted'] as $reviewStatus)
                        <option value="{{ $reviewStatus }}" {{ ($filters['review_status'] ?? '') === $reviewStatus ? 'selected' : '' }}>{{ ucfirst($reviewStatus) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">Filter</button>
                @if (array_filter($filters))
                    <a href="{{ route('admin.applicants.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition">Clear</a>
                @endif
            </div>
        </form>

        <form id="selected-applicant-export" method="POST" action="{{ route('admin.applicants.export-selected') }}"
              class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-[#17458F]/15 bg-[#17458F]/5 px-4 py-3">
            @csrf
            <template x-for="applicantId in selected" :key="applicantId">
                <input type="hidden" name="applicant_ids[]" :value="applicantId">
            </template>
            <div>
                <p class="text-sm font-semibold text-[#0b3763]"><span x-text="selected.length"></span> applicant<span x-show="selected.length !== 1">s</span> selected</p>
                <p class="text-xs text-gray-500">Choose applicants below, then download their complete records.</p>
                @error('applicant_ids')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" name="format" value="pdf" :disabled="selected.length === 0"
                        class="rounded-md border border-[#17458F]/25 bg-white px-3 py-2 text-sm font-semibold text-[#17458F] transition hover:bg-[#17458F]/5 disabled:cursor-not-allowed disabled:opacity-40">
                    Export PDF
                </button>
                <button type="submit" name="format" value="xlsx" :disabled="selected.length === 0"
                        class="rounded-md bg-[#17458F] px-3 py-2 text-sm font-semibold text-white transition hover:bg-[#123669] disabled:cursor-not-allowed disabled:opacity-40">
                    Export Spreadsheet
                </button>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="w-12 px-4 py-2.5 text-left">
                                <input type="checkbox" aria-label="Select all applicants on this page"
                                       :checked="allSelected()" @change="toggleAll($event.target.checked)"
                                       class="rounded border-gray-300 text-[#17458F] focus:ring-[#17458F]">
                            </th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Name</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Email</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Category</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Status</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Review</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Registered</th>
                            <th class="text-right px-4 py-2.5 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($applicants as $applicant)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5">
                                    <input type="checkbox" value="{{ $applicant->id }}" x-model.number="selected"
                                           aria-label="Select {{ $applicant->name }}"
                                           class="rounded border-gray-300 text-[#17458F] focus:ring-[#17458F]">
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="font-medium text-gray-800">{{ $applicant->name }}</div>
                                    @if ($applicant->company_name)
                                        <div class="text-xs text-gray-400">{{ $applicant->company_name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-600">{{ $applicant->email }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full {{ $applicant->applicant_type === 'corporate' ? 'bg-[#17458F]/10 text-[#17458F]' : 'bg-[#F7A81B]/10 text-[#a4700f]' }}">
                                        {{ ucfirst($applicant->applicant_type ?? '—') }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5">
                                    @if ($applicant->email_verified_at)
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-green-50 text-green-700">Verified</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-amber-50 text-amber-700">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    @php
                                        $reviewStatus = $applicant->application?->review_status ?? 'not_started';
                                        $reviewClasses = match ($reviewStatus) {
                                            'approved' => 'bg-green-50 text-green-700',
                                            'rejected' => 'bg-red-50 text-red-700',
                                            'blacklisted' => 'bg-gray-900 text-white',
                                            'pending' => 'bg-amber-50 text-amber-700',
                                            default => 'bg-gray-100 text-gray-500',
                                        };
                                    @endphp
                                    <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $reviewClasses }}">
                                        {{ $reviewStatus === 'not_started' ? 'Not started' : ucfirst($reviewStatus) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-500">{{ $applicant->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-2.5 text-right">
                                    @php
                                        [$actionStatusLabel, $actionStatusClasses] = match (true) {
                                            ! $applicant->application?->isSubmitted() => ['Application pending', 'bg-gray-100 text-gray-600'],
                                            $reviewStatus === 'approved' => ['Approved', 'bg-green-50 text-green-700'],
                                            $reviewStatus === 'rejected' => ['Rejected', 'bg-red-50 text-red-700'],
                                            $reviewStatus === 'blacklisted' => ['Blacklisted', 'bg-gray-900 text-white'],
                                            default => ['Pending review', 'bg-amber-50 text-amber-700'],
                                        };
                                    @endphp
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $actionStatusClasses }}">
                                            {{ $actionStatusLabel }}
                                        </span>
                                        <button type="button"
                                                @click="
                                                    open = true; loading = true; content = '';
                                                    fetch('{{ route('admin.applicants.show', $applicant) }}')
                                                        .then(r => r.text())
                                                        .then(html => { content = html; loading = false; })
                                                        .catch(() => { content = '<p class=\'text-sm text-red-600\'>Failed to load application.</p>'; loading = false; })
                                                "
                                                class="inline-flex items-center gap-1 rounded-md border border-[#17458F]/30 px-3 py-1.5 text-xs font-semibold text-[#17458F] transition hover:bg-[#17458F]/5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </button>
                                        <a href="{{ route('admin.applicants.email-logs', $applicant) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 rounded-md border border-[#17458F]/30 px-2.5 py-1.5 text-xs font-semibold text-[#17458F] transition hover:bg-[#17458F]/5"
                                           title="Open email history for {{ $applicant->name }} in a new tab">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            Email
                                        </a>
                                        @if ($reviewStatus === 'approved')
                                            <button type="button"
                                                    @click='composeEmail(@js([
                                                        "action" => route("admin.applicants.email", $applicant),
                                                        "name" => $applicant->name,
                                                        "email" => $applicant->email,
                                                    ]), "award")'
                                                    class="inline-flex items-center gap-1 rounded-md bg-[#d49b2a] px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-[#b98522]"
                                                    title="Send award notification to {{ $applicant->name }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0V4zM7 6H4v2a4 4 0 004 4m9-6h3v2a4 4 0 01-4 4"/>
                                                </svg>
                                                Award
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-400">No applicants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($applicants->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $applicants->links() }}
                </div>
            @endif
        </div>

        <!-- Award email composer -->
        <div x-show="emailOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div x-show="emailOpen" x-transition.opacity @click="emailOpen = false" class="fixed inset-0 bg-black/40"></div>

            <div x-show="emailOpen" x-transition class="relative w-full max-w-xl rounded-xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800"
                            x-text="emailType === 'award' ? 'Send award notification' : 'Email applicant'"></h3>
                        <p class="mt-0.5 text-xs text-gray-500">
                            To <span class="font-semibold" x-text="emailRecipientName"></span>
                            · <span x-text="emailRecipientEmail"></span>
                        </p>
                    </div>
                    <button type="button" @click="emailOpen = false" class="text-gray-400 transition hover:text-gray-600" aria-label="Close email composer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="emailAction" class="space-y-4 px-6 py-5"
                      @submit="if (emailType === 'award' && ! confirm('Send this winning/award notification to the applicant?')) $event.preventDefault()">
                    @csrf
                    <input type="hidden" name="message_type" :value="emailType">
                    <div x-show="emailType === 'award'" class="rounded-lg border border-[#d49b2a]/40 bg-[#d49b2a]/10 p-4">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input type="checkbox" name="mark_as_winner" value="1" x-model="emailMarkWinner"
                                   :required="emailType === 'award'"
                                   class="mt-0.5 rounded border-gray-300 text-[#d49b2a] focus:ring-[#d49b2a]">
                            <span>
                                <span class="block text-sm font-bold text-[#0b3763]">Mark this applicant as an award winner</span>
                                <span class="mt-0.5 block text-xs leading-5 text-gray-600">This confirmation is required before the winning notification can be sent.</span>
                            </span>
                        </label>
                    </div>
                    <div>
                        <label for="applicant-email-subject" class="mb-1 block text-xs font-semibold text-gray-600">Subject</label>
                        <input id="applicant-email-subject" type="text" name="subject" x-model="emailSubject" required maxlength="255"
                               class="w-full rounded-md border-gray-300 text-sm focus:border-[#17458F] focus:ring-[#17458F]">
                    </div>
                    <div>
                        <label for="applicant-email-message" class="mb-1 block text-xs font-semibold text-gray-600">Message</label>
                        <textarea id="applicant-email-message" name="message" x-model="emailMessage" required maxlength="10000" rows="10"
                                  class="w-full rounded-md border-gray-300 text-sm leading-6 focus:border-[#17458F] focus:ring-[#17458F]"></textarea>
                        <p class="mt-1 text-xs text-gray-400">Available placeholders: {name}, {email}, {application_type}, {company_name}</p>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                        <button type="button" @click="emailOpen = false" class="rounded-md px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100">Cancel</button>
                        <button type="submit" :disabled="emailType === 'award' && ! emailMarkWinner"
                                class="rounded-md px-4 py-2 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-40"
                                :class="emailType === 'award' ? 'bg-[#d49b2a] hover:bg-[#b98522]' : 'bg-[#17458F] hover:bg-[#123669]'"
                                x-text="emailType === 'award' ? 'Send Award Notification' : 'Send Email'"></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start sm:items-center justify-center p-4 overflow-y-auto">
            <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 bg-black/40"></div>

            <div x-show="open" x-transition
                 class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl my-8 max-h-[85vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                    <h3 class="text-sm font-semibold text-gray-800">Application Details</h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 overflow-y-auto">
                    <template x-if="loading">
                        <p class="text-sm text-gray-400 text-center py-8">Loading…</p>
                    </template>
                    <div x-show="!loading" x-html="content"></div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
