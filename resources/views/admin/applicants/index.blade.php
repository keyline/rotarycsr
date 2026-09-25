<x-admin-layout :title="'Applicants'">
    @php
        $showAwardActions = false;
    @endphp

    <div x-data="{
        open: false,
        loading: false,
        content: '',
        awardConfirmOpen: false,
        awardAction: '',
        awardApplicantName: '',
        awardApplicantEmail: '',
        emailOpen: false,
        emailAction: '',
        emailRecipientName: '',
        emailRecipientEmail: '',
        emailSubject: '',
        emailMessage: '',
        selected: [],
        visibleIds: @js($applicants->pluck('id')->values()),
        allSelected() {
            return this.visibleIds.length > 0 && this.visibleIds.every(id => this.selected.includes(id));
        },
        toggleAll(checked) {
            this.selected = checked ? [...this.visibleIds] : [];
        },
        confirmAward(applicant) {
            this.awardAction = applicant.action;
            this.awardApplicantName = applicant.name;
            this.awardApplicantEmail = applicant.email;
            this.awardConfirmOpen = true;
        },
        composeAwardEmail(applicant) {
            this.emailAction = applicant.action;
            this.emailRecipientName = applicant.name;
            this.emailRecipientEmail = applicant.email;
            this.emailSubject = @js(\App\Services\ApplicantMessageMailer::AWARD_SUBJECT);
            this.emailMessage = @js(\App\Services\ApplicantMessageMailer::AWARD_MESSAGE);
            this.emailOpen = true;
        }
    }" @keydown.escape.window="open = false; awardConfirmOpen = false; emailOpen = false">
        <!-- Filters -->
        <form method="GET" class="bg-white border border-gray-200 rounded-lg p-4 mb-4 flex flex-wrap items-end gap-3">
            <div class="min-w-0 flex-1 basis-full sm:min-w-[200px] sm:basis-auto">
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
            <div class="flex flex-wrap items-center gap-2">
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
                <table class="w-full min-w-[1360px] table-fixed text-[13px]">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="w-10 px-3 py-2.5 text-left">
                                <input type="checkbox" aria-label="Select all applicants on this page"
                                       :checked="allSelected()" @change="toggleAll($event.target.checked)"
                                       class="rounded border-gray-300 text-[#17458F] focus:ring-[#17458F]">
                            </th>
                            <th class="w-40 px-3 py-2.5 text-left text-xs font-semibold text-gray-500">Application Number</th>
                            <th class="w-36 px-3 py-2.5 text-left text-xs font-semibold text-gray-500">Name</th>
                            <th class="w-36 px-3 py-2.5 text-left text-xs font-semibold text-gray-500">Company</th>
                            <th class="w-56 px-3 py-2.5 text-left text-xs font-semibold text-gray-500">Email</th>
                            <th class="w-28 px-3 py-2.5 text-left text-xs font-semibold text-gray-500">Category</th>
                            <th class="w-28 px-3 py-2.5 text-left text-xs font-semibold text-gray-500">Status</th>
                            <th class="w-28 px-3 py-2.5 text-left text-xs font-semibold text-gray-500">Review</th>
                            <th class="w-28 px-3 py-2.5 text-left text-xs font-semibold text-gray-500">Registered</th>
                            <th class="w-44 px-3 py-2.5 text-right text-xs font-semibold text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($applicants as $applicant)
                            @php
                                $reviewStatus = $applicant->application?->review_status ?? 'not_started';
                                $reviewClasses = match ($reviewStatus) {
                                    'approved' => 'bg-green-50 text-green-700',
                                    'rejected' => 'bg-red-50 text-red-700',
                                    'blacklisted' => 'bg-gray-900 text-white',
                                    'pending' => 'bg-amber-50 text-amber-700',
                                    default => 'bg-gray-100 text-gray-500',
                                };
                                $isAwardWinner = $applicant->application?->award_winner_at !== null;
                                $awardEmailSent = (bool) $applicant->award_email_sent;
                                $awardComplete = $isAwardWinner && $awardEmailSent;
                            @endphp
                            <tr data-applicant-row="{{ $applicant->id }}"
                                data-award-complete="{{ $awardComplete ? 'true' : 'false' }}"
                                class="transition {{ $awardComplete ? 'bg-green-50 hover:bg-green-100' : 'hover:bg-gray-50' }}">
                                <td class="px-3 py-2 align-middle">
                                    <input type="checkbox" value="{{ $applicant->id }}" x-model.number="selected"
                                           aria-label="Select {{ $applicant->name }}"
                                           class="rounded border-gray-300 text-[#17458F] focus:ring-[#17458F]">
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 align-middle">
                                    <span class="font-semibold text-[#17458F]">{{ $applicant->application?->reference_number ?: '—' }}</span>
                                </td>
                                <td class="min-w-0 px-3 py-2 align-middle">
                                    <div class="truncate font-medium text-gray-800" title="{{ $applicant->name }}">{{ $applicant->name }}</div>
                                </td>
                                <td class="min-w-0 px-3 py-2 align-middle text-gray-600">
                                    <div class="truncate" title="{{ $applicant->company_name ?: '—' }}">{{ $applicant->company_name ?: '—' }}</div>
                                </td>
                                <td class="min-w-0 px-3 py-2 align-middle text-gray-600">
                                    <div class="truncate" title="{{ $applicant->email }}">{{ $applicant->email }}</div>
                                </td>
                                <td class="px-3 py-2 align-middle">
                                    <span class="inline-block whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium {{ $applicant->applicant_type === 'corporate' ? 'bg-[#17458F]/10 text-[#17458F]' : 'bg-[#F7A81B]/10 text-[#a4700f]' }}">
                                        {{ ucfirst($applicant->applicant_type ?? '—') }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 align-middle">
                                    @if ($applicant->email_verified_at)
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-green-50 text-green-700">Verified</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-amber-50 text-amber-700">Pending</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 align-middle">
                                    <span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $reviewClasses }}">
                                        {{ $reviewStatus === 'not_started' ? 'Not started' : ucfirst($reviewStatus) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 align-middle text-gray-500">
                                    {{ $applicant->created_at->format('d M Y') }}
                                </td>
                                <td class="px-3 py-2 text-right align-middle">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        @if ($isAwardWinner)
                                            <span class="inline-flex rounded-full bg-[#d49b2a]/15 px-2.5 py-1 text-xs font-semibold text-[#8a5d08]">
                                                Award Winner
                                            </span>
                                        @endif
                                        @if ($awardEmailSent)
                                            <span class="inline-flex rounded-full bg-green-600 px-2.5 py-1 text-xs font-semibold text-white">
                                                Award Email Sent
                                            </span>
                                        @endif
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
                                        @if ($showAwardActions)
                                            @if ($reviewStatus === 'approved' && ! $isAwardWinner)
                                                <button type="button"
                                                        @click="confirmAward(@js([
                                                            'action' => route('admin.applications.award-winner', $applicant->application),
                                                            'name' => $applicant->name,
                                                            'email' => $applicant->email,
                                                        ]))"
                                                        class="inline-flex items-center gap-1 rounded-md bg-[#d49b2a] px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-[#b98522]"
                                                        title="Mark {{ $applicant->name }} as an award winner">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0V4zM7 6H4v2a4 4 0 004 4m9-6h3v2a4 4 0 01-4 4"/>
                                                    </svg>
                                                    Award
                                                </button>
                                            @elseif ($isAwardWinner && ! $awardEmailSent)
                                                <button type="button"
                                                        @click="composeAwardEmail(@js([
                                                            'action' => route('admin.applicants.email', $applicant),
                                                            'name' => $applicant->name,
                                                            'email' => $applicant->email,
                                                        ]))"
                                                        class="inline-flex items-center gap-1 rounded-md bg-[#17458F] px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-[#123669]"
                                                        title="Send award email to {{ $applicant->name }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    Award Email
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-400">No applicants found.</td>
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

        @if ($showAwardActions)
            <!-- Award winner confirmation -->
            <div x-show="awardConfirmOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div x-show="awardConfirmOpen" x-transition.opacity @click="awardConfirmOpen = false" class="fixed inset-0 bg-black/40"></div>

            <div x-show="awardConfirmOpen" x-transition role="dialog" aria-modal="true" aria-labelledby="award-confirm-title"
                 class="relative w-full max-w-md rounded-xl bg-white shadow-xl">
                <div class="px-6 py-6 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#d49b2a]/15 text-[#b47b13]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0V4zM7 6H4v2a4 4 0 004 4m9-6h3v2a4 4 0 01-4 4"/>
                        </svg>
                    </div>
                    <h3 id="award-confirm-title" class="mt-4 text-lg font-bold text-gray-900">Mark as award winner?</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Confirm that <span class="font-semibold text-gray-900" x-text="awardApplicantName"></span>
                        (<span x-text="awardApplicantEmail"></span>) has been selected for an award.
                    </p>
                    <p class="mt-2 text-xs leading-5 text-gray-500">No email will be sent automatically. You can compose and send the award email afterward.</p>
                </div>

                <form method="POST" :action="awardAction" class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4">
                    @csrf
                    <button type="button" @click="awardConfirmOpen = false"
                            class="rounded-md px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-md bg-[#d49b2a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#b98522]">
                        Yes, Mark as Winner
                    </button>
                </form>
            </div>
            </div>

            <!-- Award email composer -->
            <div x-show="emailOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div x-show="emailOpen" x-transition.opacity @click="emailOpen = false" class="fixed inset-0 bg-black/40"></div>

            <div x-show="emailOpen" x-transition class="relative w-full max-w-xl rounded-xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Send award email</h3>
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

                <form method="POST" :action="emailAction" class="space-y-4 px-6 py-5">
                    @csrf
                    <input type="hidden" name="message_type" value="award">
                    <div class="rounded-lg border border-[#d49b2a]/40 bg-[#d49b2a]/10 p-4 text-sm leading-6 text-gray-700">
                        This applicant is already marked as an award winner. Review or customize the subject and message before sending.
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
                        <p class="mt-1 text-xs text-gray-400">Available placeholders: {name}, {email}, {application_type}, {company_name}, {organization_name}, {award_name}</p>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                        <button type="button" @click="emailOpen = false" class="rounded-md px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100">Cancel</button>
                        <button type="submit" class="rounded-md bg-[#17458F] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#123669]">
                            Send Award Email
                        </button>
                    </div>
                </form>
            </div>
            </div>
        @endif

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
