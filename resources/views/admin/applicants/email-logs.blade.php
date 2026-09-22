<x-admin-layout :title="'Email History'">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 rounded-lg border border-gray-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-wider text-[#17458F]">Applicant email history</p>
                <h2 class="mt-1 truncate text-xl font-bold text-gray-900">{{ $applicant->name }}</h2>
                <p class="mt-1 break-all text-sm text-gray-500">{{ $applicant->email }}</p>
            </div>
            <a href="{{ route('admin.applicants.index') }}"
               class="inline-flex shrink-0 items-center justify-center rounded-md border border-[#17458F]/25 px-4 py-2 text-sm font-semibold text-[#17458F] transition hover:bg-[#17458F]/5">
                Back to Applicants
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="space-y-4 lg:col-span-2">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Sent emails</h3>
                        <p class="text-xs text-gray-500">Only successfully delivered emails are recorded.</p>
                    </div>
                    <span class="rounded-full bg-[#17458F]/10 px-2.5 py-1 text-xs font-semibold text-[#17458F]">
                        {{ $emailLogs->total() }} {{ \Illuminate\Support\Str::plural('email', $emailLogs->total()) }}
                    </span>
                </div>

                @forelse ($emailLogs as $emailLog)
                    @php
                        $typeClasses = match ($emailLog->type) {
                            'approved' => 'bg-green-50 text-green-700',
                            'rejected' => 'bg-red-50 text-red-700',
                            'blacklisted' => 'bg-gray-900 text-white',
                            'award' => 'bg-[#d49b2a]/10 text-[#9a6b0d]',
                            default => 'bg-[#17458F]/10 text-[#17458F]',
                        };
                    @endphp
                    <article class="rounded-lg border border-gray-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $typeClasses }}">
                                {{ ucfirst(str_replace('_', ' ', $emailLog->type)) }}
                            </span>
                            <time class="text-xs text-gray-400" datetime="{{ $emailLog->sent_at->toIso8601String() }}">
                                {{ $emailLog->sent_at->format('d M Y, h:i A') }}
                            </time>
                        </div>
                        <h4 class="mt-3 break-words text-sm font-bold text-gray-900">{{ $emailLog->subject }}</h4>
                        <p class="mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-gray-600">{{ $emailLog->bodyAsText() }}</p>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 bg-white px-5 py-12 text-center">
                        <p class="text-sm font-semibold text-gray-700">No emails sent yet</p>
                        <p class="mt-1 text-xs text-gray-400">Decision, award, and direct emails will appear here after successful delivery.</p>
                    </div>
                @endforelse

                @if ($emailLogs->hasPages())
                    <div class="rounded-lg border border-gray-200 bg-white px-4 py-3">
                        {{ $emailLogs->links() }}
                    </div>
                @endif
            </section>

            <aside class="h-fit rounded-lg border border-gray-200 bg-white p-5 lg:sticky lg:top-6">
                <h3 class="text-base font-bold text-gray-900">Compose email</h3>
                <p class="mt-1 text-xs leading-5 text-gray-500">Send a direct message to this applicant.</p>

                <form method="POST" action="{{ route('admin.applicants.email', $applicant) }}" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="message_type" value="general">

                    <div>
                        <label for="applicant-email-subject" class="mb-1 block text-xs font-semibold text-gray-600">Subject</label>
                        <input id="applicant-email-subject" type="text" name="subject" value="{{ old('subject') }}" required maxlength="255"
                               class="w-full rounded-md border-gray-300 text-sm focus:border-[#17458F] focus:ring-[#17458F]">
                        @error('subject')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="applicant-email-message" class="mb-1 block text-xs font-semibold text-gray-600">Message</label>
                        <textarea id="applicant-email-message" name="message" required maxlength="10000" rows="12"
                                  class="w-full rounded-md border-gray-300 text-sm leading-6 focus:border-[#17458F] focus:ring-[#17458F]">{{ old('message', $defaultMessage) }}</textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">Placeholders: {name}, {email}, {application_type}, {company_name}</p>
                    </div>

                    <button type="submit" class="w-full rounded-md bg-[#17458F] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#123669]">
                        Send Email
                    </button>
                </form>
            </aside>
        </div>
    </div>
</x-admin-layout>
