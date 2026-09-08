<x-guest-layout>
    <div class="flex items-center justify-between mb-4 px-3 py-2 bg-[#17458F]/5 border border-[#17458F]/20 rounded-md">
        <span class="text-sm text-gray-700">
            Applying as
            <span class="font-semibold text-[#17458F]">
                {{ $applicantType === 'corporate' ? 'Corporate Excellence Award' : 'CSR Leader of the Year (Individual)' }}
            </span>
        </span>
        <a href="{{ route('apply') }}" class="text-xs font-semibold text-[#17458F] underline hover:text-[#123669]">Change</a>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input type="hidden" name="applicant_type" value="{{ $applicantType }}">

        @if ($applicantType === 'corporate')
            <!-- Company Name -->
            <div>
                <x-input-label for="company_name" :value="__('Company / Foundation Name')" />
                <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name')" required autofocus autocomplete="organization" />
                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
            </div>

            <!-- Contact Name -->
            <div class="mt-4">
                <x-input-label for="name" :value="__('Contact Person Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
        @else
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
        @endif

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500">We'll send a 6-digit verification code to this address.</p>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Send Verification Code') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
