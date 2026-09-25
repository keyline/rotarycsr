<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        We've sent a 6-digit verification code to <span class="break-all font-semibold text-gray-800">{{ $email }}</span>.
        Enter it below to continue.
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 text-sm font-medium text-red-600">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.otp') }}">
        @csrf

        <div>
            <x-input-label for="otp" :value="__('Verification Code')" />
            <x-text-input id="otp" class="block mt-1 w-full text-center tracking-[0.5em] text-lg" type="text"
                          inputmode="numeric" pattern="[0-9]*" maxlength="6" name="otp" required autofocus />
            <x-input-error :messages="$errors->get('otp')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('verification.otp.resend') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm underline text-gray-600 hover:text-gray-900">
            {{ __("Didn't get a code? Resend") }}
        </button>
    </form>
</x-guest-layout>
