<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        <span class="break-all font-semibold text-gray-800">{{ $email }}</span> is verified. Set a password to finish creating your account.
    </div>

    <form method="POST" action="{{ route('register.password') }}">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-password-input id="password" class="block w-full" name="password" required autofocus autocomplete="new-password" maxlength="10" />
            <p class="mt-2 text-sm text-gray-600">Use 8 to 10 characters.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-password-input id="password_confirmation" class="block w-full" name="password_confirmation" required autocomplete="new-password" maxlength="10" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Create Account') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
