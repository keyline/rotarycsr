<x-admin-layout :title="'Applicants'">
    <x-slot name="actions">
        <a href="{{ route('admin.applicants.export', request()->query()) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
            </svg>
            Export CSV
        </a>
    </x-slot>

    <div x-data="{ open: false, loading: false, content: '' }" @keydown.escape.window="open = false">
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

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">Filter</button>
                @if (array_filter($filters))
                    <a href="{{ route('admin.applicants.index') }}" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-900 transition">Clear</a>
                @endif
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Name</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Email</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Category</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Status</th>
                            <th class="text-left px-4 py-2.5 font-medium text-gray-500">Registered</th>
                            <th class="text-right px-4 py-2.5 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($applicants as $applicant)
                            <tr class="hover:bg-gray-50">
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
                                <td class="px-4 py-2.5 text-gray-500">{{ $applicant->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-2.5 text-right">
                                    <button type="button"
                                            @click="
                                                open = true; loading = true; content = '';
                                                fetch('{{ route('admin.applicants.show', $applicant) }}')
                                                    .then(r => r.text())
                                                    .then(html => { content = html; loading = false; })
                                                    .catch(() => { content = '<p class=\'text-sm text-red-600\'>Failed to load application.</p>'; loading = false; })
                                            "
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-[#17458F] border border-[#17458F]/30 rounded-md hover:bg-[#17458F]/5 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">No applicants found.</td>
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
