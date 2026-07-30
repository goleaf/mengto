<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-4xl gap-7">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ route('care-journals.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                {{ __('ui.care_journals_efcbb402a3') }}
            </a>
            <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.private_by_default_f52e06762e') }}</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.create_a_care_journal_593ab2e50b') }}</h1>
            <p class="mt-2 max-w-2xl text-paw-muted">{{ __('ui.choose_one_managed_pet_daily_records_stay_separate_b10f58329f') }}</p>
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>{{ __('ui.the_journal_was_not_created_e807e191b5') }}</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>{{ __('ui.validation_failed_fa0dce7e0b') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('care-journals.store') }}" class="care-form-section">
            @csrf
            <div class="care-form-grid">
                <label>
                    {{ __('ui.managed_pet_a2e0844927') }}
                    <select name="pet_profile_key" required>
                        <option value="">{{ __('ui.choose_a_pet_3c0fc54293') }}</option>
                        @forelse ($pet_options as $key => $label)
                            <option value="{{ $key }}" @selected(old('pet_profile_key') === $key)>{{ $label }}</option>
                        @empty
                            <option value="" disabled>{{ __('ui.every_managed_pet_already_has_a_journal_1aacd95ddc') }}</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    {{ __('ui.timezone_4ceca1d52c') }}
                    <input name="timezone" value="{{ old('timezone', $timezone) }}" required>
                </label>
                <label>
                    {{ __('ui.responsible_today_49071425c7') }}
                    <input name="current_caregiver_name" value="{{ old('current_caregiver_name', __('ui.mia_carter_0e5b29cc3b')) }}" maxlength="120">
                </label>
            </div>

            <label class="care-check care-check--boxed">
                <input type="checkbox" name="privacy_acknowledged" value="1" required @checked(old('privacy_acknowledged'))>
                <span>{{ __('ui.i_understand_this_journal_is_private_and_access_4329284221') }}</span>
            </label>

            <button type="submit" class="action action--primary" @disabled($pet_options === [])>
                <x-lucide-lock-keyhole class="icon" aria-hidden="true" />
                <span>{{ __('ui.create_private_journal_fae55507cb') }}</span>
            </button>
        </form>
    </div>
</x-app-shell>
