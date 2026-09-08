<x-admin-layout :title="'Activity Log'">
    <form method="GET" class="mb-4">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search by event, description, or email…"
               class="w-full max-w-sm text-sm border-gray-300 rounded-md focus:border-[#17458F] focus:ring-[#17458F]">
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500 whitespace-nowrap">Date / Time</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500">Activity</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500 whitespace-nowrap">User</th>
                        <th class="text-left px-4 py-2.5 font-medium text-gray-500 whitespace-nowrap">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap" title="{{ $log->created_at->diffForHumans() }}">
                                {{ $log->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full shrink-0
                                        {{ str_contains($log->event, 'failed') ? 'bg-red-500' : (str_starts_with($log->event, 'admin.') ? 'bg-[#F7A81B]' : 'bg-[#17458F]') }}">
                                    </span>
                                    <span class="text-gray-700">{{ $log->description ?? $log->event }}</span>
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5 pl-3.5">{{ $log->event }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 whitespace-nowrap">{{ $log->causer_email ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-400 whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">No activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
