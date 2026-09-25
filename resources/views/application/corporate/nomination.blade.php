<x-application-step :application="$application" :step="$step" :total-steps="$totalSteps" :step-key="$stepKey" :locked="$locked">
    <section>
        <h3 class="font-display text-xl font-bold text-rotary-navy">Corporate / Foundation Details</h3>
        <div class="mt-4 space-y-5">
            <div>
                <x-input-label for="corporate_foundation_name" value="Corporate / Foundation Name" />
                <x-text-input id="corporate_foundation_name" name="corporate_foundation_name" type="text" class="block mt-1 w-full"
                              :value="old('corporate_foundation_name', $application->corporate_foundation_name)" required />
                <x-input-error :messages="$errors->get('corporate_foundation_name')" class="mt-1" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="csr_registration_number" value="CSR Registration Number" />
                    <x-text-input id="csr_registration_number" name="csr_registration_number" type="text" class="block mt-1 w-full"
                                  :value="old('csr_registration_number', $application->csr_registration_number)" required />
                    <x-input-error :messages="$errors->get('csr_registration_number')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="industry_sector" value="Industry / Sector" />
                    <x-text-input id="industry_sector" name="industry_sector" type="text" class="block mt-1 w-full"
                                  :value="old('industry_sector', $application->industry_sector)" required />
                    <x-input-error :messages="$errors->get('industry_sector')" class="mt-1" />
                </div>
            </div>

            <div>
                <x-input-label for="head_office_location" value="Registered / Head Office Address" />
                <textarea id="head_office_location" name="head_office_location" rows="3" required
                          class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('head_office_location', $application->head_office_location) }}</textarea>
                <x-input-error :messages="$errors->get('head_office_location')" class="mt-1" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="corporate_presence" value="Presence" />
                    <select id="corporate_presence" name="corporate_presence" required
                            class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select one</option>
                        @foreach (\App\Services\ApplicationOptions::CORPORATE_PRESENCE_OPTIONS as $value => $label)
                            <option value="{{ $value }}" @selected(old('corporate_presence', $application->corporate_presence) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Select National, Regional, or State.</p>
                    <x-input-error :messages="$errors->get('corporate_presence')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="business_group_name" value="Name of Business Group" />
                    <x-text-input id="business_group_name" name="business_group_name" type="text" class="block mt-1 w-full"
                                  :value="old('business_group_name', $application->business_group_name)" />
                    <x-input-error :messages="$errors->get('business_group_name')" class="mt-1" />
                </div>
            </div>
        </div>
    </section>

    @foreach ([
        'primary' => ['title' => 'Primary Contact', 'required' => true],
        'secondary' => ['title' => 'Secondary Contact', 'required' => false],
    ] as $contact => $details)
        <section class="border-t border-gray-100 pt-6">
            <h3 class="font-display text-xl font-bold text-rotary-navy">{{ $details['title'] }}</h3>
            @unless ($details['required'])
                <p class="mt-1 text-sm text-gray-500">Optional. If entered, please complete all four fields.</p>
            @endunless
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label :for="$contact.'_contact_name'" value="Name" />
                    <x-text-input :id="$contact.'_contact_name'" :name="$contact.'_contact_name'" type="text" class="block mt-1 w-full"
                                  :value="old($contact.'_contact_name', $application->{$contact.'_contact_name'})" :required="$details['required']" />
                    <x-input-error :messages="$errors->get($contact.'_contact_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label :for="$contact.'_contact_designation'" value="Designation" />
                    <x-text-input :id="$contact.'_contact_designation'" :name="$contact.'_contact_designation'" type="text" class="block mt-1 w-full"
                                  :value="old($contact.'_contact_designation', $application->{$contact.'_contact_designation'})" :required="$details['required']" />
                    <x-input-error :messages="$errors->get($contact.'_contact_designation')" class="mt-1" />
                </div>
                <div>
                    <x-input-label :for="$contact.'_contact_email'" value="Email ID" />
                    <x-text-input :id="$contact.'_contact_email'" :name="$contact.'_contact_email'" type="email" class="block mt-1 w-full"
                                  :value="old($contact.'_contact_email', $application->{$contact.'_contact_email'})" :required="$details['required']" />
                    <x-input-error :messages="$errors->get($contact.'_contact_email')" class="mt-1" />
                </div>
                <div>
                    <x-input-label :for="$contact.'_contact_mobile'" value="Mobile Number" />
                    <x-text-input :id="$contact.'_contact_mobile'" :name="$contact.'_contact_mobile'" type="tel" class="block mt-1 w-full"
                                  :value="old($contact.'_contact_mobile', $application->{$contact.'_contact_mobile'})" :required="$details['required']" />
                    <x-input-error :messages="$errors->get($contact.'_contact_mobile')" class="mt-1" />
                </div>
            </div>
        </section>
    @endforeach
</x-application-step>
