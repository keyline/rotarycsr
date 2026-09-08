<x-admin-layout :title="'Settings'">
    <div class="grid lg:grid-cols-2 gap-6 max-w-4xl">
        <!-- Site logo -->
        <div class="bg-white rounded-lg border border-gray-200 p-5 h-fit">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Site Logo</h2>
            <p class="text-xs text-gray-500 mb-4">
                Shown across the site header, login/register pages, and the admin sidebar. PNG, JPG, SVG, or WEBP — max 2MB.
            </p>

            <div class="flex items-center gap-4 mb-4 p-4 bg-gray-50 rounded-md border border-gray-100">
                <x-site-logo class="h-10 w-auto" />
                <span class="text-xs text-gray-400">{{ $logoPath ? 'Custom logo' : 'Default logo' }}</span>
            </div>

            <form method="POST" action="{{ route('admin.settings.logo.update') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <input type="file" name="logo" accept=".png,.jpg,.jpeg,.svg,.webp"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#17458F]/10 file:text-[#17458F] hover:file:bg-[#17458F]/20" required>
                @error('logo')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="w-full px-3 py-2 text-sm font-semibold text-white bg-[#17458F] rounded-md hover:bg-[#123669] transition">
                    Upload Logo
                </button>
            </form>

            @if ($logoPath)
                <form method="POST" action="{{ route('admin.settings.logo.remove') }}" class="mt-2"
                      onsubmit="return confirm('Reset to the default Rotary logo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-3 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-md hover:bg-gray-50 transition">
                        Reset to Default Logo
                    </button>
                </form>
            @endif
        </div>

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
</x-admin-layout>
