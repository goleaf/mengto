<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-4xl gap-7">
        <x-page-header
            :eyebrow="__('ui.private_by_default')"
            :title="__('ui.create_a_care_journal')"
            :description="__('ui.choose_one_managed_pet_daily_records_stay_separate_from_the_public_profile_and_professional_medical_history')"
            heading-id="create-care-journal-heading"
            :action-label="__('ui.care_journals')"
            action-icon="arrow-left"
            :action-href="route('care-journals.index')"
            action-variant="paper"
            data-section="care-journal-create-header"
        />

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_journal_was_not_created') }}</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>{{ __('ui.validation_failed') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('care-journals.store') }}" class="care-form-section">
            @csrf
            <div class="care-form-grid">
                <label>
                    {{ __('ui.managed_pet') }}
                    <select name="pet_profile_key" required>
                        <option value="">{{ __('ui.choose_a_pet') }}</option>
                        @forelse ($pet_options as $key => $label)
                            <option value="{{ $key }}" @selected(old('pet_profile_key') === $key)>{{ $label }}</option>
                        @empty
                            <option value="" disabled>{{ __('ui.every_managed_pet_already_has_a_journal') }}</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    {{ __('ui.timezone') }}
                    <input name="timezone" value="{{ old('timezone', $timezone) }}" required>
                </label>
                <label>
                    {{ __('ui.responsible_today') }}
                    <input name="current_caregiver_name" value="{{ old('current_caregiver_name', __('ui.mia_carter')) }}" maxlength="120">
                </label>
            </div>

            <label class="care-check care-check--boxed">
                <input type="checkbox" name="privacy_acknowledged" value="1" required @checked(old('privacy_acknowledged'))>
                <span>{{ __('ui.i_understand_this_journal_is_private_and_access_is_granted_explicitly_to_each_family_member_sitter_or_specialist') }}</span>
            </label>

            <button type="submit" class="action action--primary" @disabled($pet_options === [])>
                <x-ui-icon name="lock-keyhole" />
                <span>{{ __('ui.create_private_journal') }}</span>
            </button>
        </form>
    </div>
</x-app-shell>
