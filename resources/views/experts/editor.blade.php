<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <x-page-header
            :eyebrow="__('ui.verified_professional_community_f3f93b61ff')"
            :title="$expert !== null ? __('ui.edit_professional_profile_9016d6cd8b') : __('ui.create_a_professional_profile_e3e6352e7a')"
            :description="__('ui.describe_only_the_work_you_are_qualified_to_3b5dee409b')"
            heading-id="expert-editor-heading"
            :action-label="$expert !== null ? __('ui.back_to_profile_d5647f3659') : __('ui.expert_directory_868fdd0c8b')"
            action-icon="arrow-left"
            :action-href="$expert !== null ? route('experts.show', $expert) : route('experts.index')"
            action-variant="paper"
            data-section="expert-editor-header"
        />

        @if ($errors->any())
            <section class="rounded-md border border-red-300 bg-red-50 p-4 text-red-950" role="alert">
                <h2 class="font-bold">{{ __('ui.please_correct_the_highlighted_information_7471d46a87') }}</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>{{ __('ui.check_the_form_and_try_again_51f017403f') }}</li>
                    @endforelse
                </ul>
            </section>
        @endif

        <form
            method="POST"
            action="{{ $expert !== null ? route('experts.update', $expert) : route('experts.store') }}"
            enctype="multipart/form-data"
            class="grid gap-8"
        >
            @csrf
            @if ($expert !== null)
                @method('PUT')
            @endif

            <section class="grid gap-4" aria-labelledby="identity-section">
                <div>
                    <h2 id="identity-section" class="text-xl font-bold">{{ __('ui.public_identity_284303e3ab') }}</h2>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('ui.your_legal_name_stays_private_and_is_used_be92374b88') }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.public_professional_name_aeb098b033') }}
                        <input name="public_name" required value="{{ old('public_name', $expert?->public_name) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @error('public_name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.legal_name_07f883c3b6') }}
                        <input name="legal_name" value="{{ old('legal_name', $expert?->legal_name) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.professional_type_3617bd6b5a') }}
                        <select name="primary_type" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('primary_type', $expert?->primary_type) === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_professional_types_cd09edcc73') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.years_of_relevant_experience_80bfab9f6d') }}
                        <input type="number" name="years_experience" required min="0" max="80" value="{{ old('years_experience', $expert?->years_experience ?? 0) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.professional_headline_f0d772964c') }}
                    <input name="headline" required maxlength="180" value="{{ old('headline', $expert?->headline) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.avian_veterinarian_focused_on_preventive_care_and_low_197c95aa46') }}">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.detailed_biography_31d5ed4a7e') }}
                    <textarea name="bio" required minlength="80" rows="6" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('bio', $expert?->bio) }}</textarea>
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.working_approach_1dc615754c') }}
                        <textarea name="approach" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('approach', $expert?->approach) }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.professional_boundaries_8628d30a06') }}
                        <textarea name="boundaries" required minlength="20" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.what_you_do_not_treat_diagnose_prescribe_or_8c732c289e') }}">{{ old('boundaries', $expert?->boundaries) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="grid gap-5 border-y border-paw-line py-7" aria-labelledby="scope-section">
                <div>
                    <h2 id="scope-section" class="text-xl font-bold">{{ __('ui.competence_and_species_66d154d844') }}</h2>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('ui.choose_precise_areas_a_checked_veterinarian_badge_will_f3b937344b') }}</p>
                </div>

                <fieldset>
                    <legend class="text-sm font-bold">{{ __('ui.specializations_b2561b50e1') }}</legend>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($specializations as $value => $label)
                            <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="specializations[]" value="{{ $value }}" @checked(in_array($value, old('specializations', $expert?->specializations ?? []), true)) class="mt-0.5 size-4 rounded border-paw-line text-paw-leaf"><span>{{ $label }}</span></label>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_specialization_options_5cab418607') }}</p>
                        @endforelse
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-sm font-bold">{{ __('ui.animal_species_1aac59a8d1') }}</legend>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        @forelse ($species_options as $value => $label)
                            <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="species[]" value="{{ $value }}" @checked(in_array($value, old('species', $expert?->species ?? []), true)) class="mt-0.5 size-4 rounded border-paw-line text-paw-leaf"><span>{{ $label }}</span></label>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_species_options_3f7adb8205') }}</p>
                        @endforelse
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-sm font-bold">{{ __('ui.age_groups_9d6bb169e6') }}</legend>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @forelse ($age_groups as $value => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="age_groups[]" value="{{ $value }}" @checked(in_array($value, old('age_groups', $expert?->age_groups ?? []), true)) class="size-4 rounded border-paw-line text-paw-leaf">{{ $label }}</label>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_age_group_options_6b56fd241e') }}</p>
                        @endforelse
                    </div>
                </fieldset>
            </section>

            <section class="grid gap-5" aria-labelledby="practice-section">
                <h2 id="practice-section" class="text-xl font-bold">{{ __('ui.practice_details_df2973523b') }}</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.country_701d021d08') }}
                        <input name="country" required value="{{ old('country', $expert?->country ?? __('ui.lithuania_5c83a631bc')) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.city_fc33f73246') }}
                        <input name="city" required value="{{ old('city', $expert?->city ?? __('ui.vilnius_c283e0869a')) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.service_area_72aa13fe85') }}
                        <input name="service_area" value="{{ old('service_area', $expert?->service_area) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.vilnius_and_20_km_radius_4441637f39') }}">
                    </label>
                </div>

                <fieldset>
                    <legend class="text-sm font-bold">{{ __('ui.consultation_languages_62543690e1') }}</legend>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @forelse ($languages as $value => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="languages[]" value="{{ $value }}" @checked(in_array($value, old('languages', $expert?->languages ?? []), true)) class="size-4 rounded border-paw-line text-paw-leaf">{{ $label }}</label>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_language_options_948b428c18') }}</p>
                        @endforelse
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-sm font-bold">{{ __('ui.work_formats_535c5b5df3') }}</legend>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @forelse ($formats as $value => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="formats[]" value="{{ $value }}" @checked(in_array($value, old('formats', $expert?->formats ?? []), true)) class="size-4 rounded border-paw-line text-paw-leaf">{{ $label }}</label>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_format_options_cf60350706') }}</p>
                        @endforelse
                    </div>
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="grid gap-2">
                        <legend class="text-sm font-bold">{{ __('ui.methods_and_principles_57429c1351') }}</legend>
                        @for ($index = 0; $index < 3; $index++)
                            <input name="methods[]" value="{{ old('methods.'.$index, $expert?->methods[$index] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('presentation.method_or_principle', ['number' => $index + 1]) }}">
                        @endfor
                    </fieldset>
                    <fieldset>
                        <legend class="text-sm font-bold">{{ __('ui.place_accessibility_607dca4bda') }}</legend>
                        <div class="mt-3 grid gap-2">
                            @forelse ($accessibility_options as $value => $label)
                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="accessibility[]" value="{{ $value }}" @checked(in_array($value, old('accessibility', $expert?->accessibility ?? []), true)) class="size-4 rounded border-paw-line text-paw-leaf">{{ $label }}</label>
                            @empty
                                <p class="text-sm text-paw-muted">{{ __('ui.no_accessibility_options_646bcf6c5b') }}</p>
                            @endforelse
                        </div>
                    </fieldset>
                </div>

                <div class="grid gap-4 sm:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.availability_12f67f8539') }}
                        <select name="availability_status" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @foreach (['available' => __('ui.available_e674447337'), 'limited' => __('ui.limited_e5125d9f63'), 'waitlist' => __('ui.waitlist_ec08d977c6'), 'unavailable' => __('ui.unavailable_ca18449697')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('availability_status', $expert?->availability_status ?? 'available') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.typical_response_4a5dce8b7a') }}
                        <input name="response_time" value="{{ old('response_time', $expert?->response_time) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.within_one_business_day_80be58657a') }}">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.price_from_8ae1d50ba1') }}
                        <input type="number" name="price_from" min="0" step="0.01" value="{{ old('price_from', $expert?->price_from) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.currency_3ac1a9ec4f') }}
                        <input name="currency" required maxlength="3" value="{{ old('currency', $expert?->currency ?? 'EUR') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5 uppercase">
                    </label>
                </div>
                <div class="flex flex-wrap gap-5">
                    <input type="hidden" name="accepts_new_clients" value="0">
                    <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="accepts_new_clients" value="1" @checked((bool) old('accepts_new_clients', $expert?->accepts_new_clients ?? true)) class="size-4 rounded border-paw-line text-paw-leaf"> {{ __('ui.accepting_new_clients_4f82bc4708') }}</label>
                    <input type="hidden" name="offers_emergency_care" value="0">
                    <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="offers_emergency_care" value="1" @checked((bool) old('offers_emergency_care', $expert?->offers_emergency_care ?? false)) class="size-4 rounded border-paw-line text-paw-leaf"> {{ __('ui.provides_verified_emergency_care_93e0279605') }}</label>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">{{ __('ui.avatar_image_url_986aea14ed') }}<input type="url" name="avatar_url" value="{{ old('avatar_url', $expert?->avatar_url) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                    <label class="grid gap-1 text-sm font-semibold">{{ __('ui.cover_image_url_6aeec91b65') }}<input type="url" name="cover_url" value="{{ old('cover_url', $expert?->cover_url) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                </div>
            </section>

            @unless ($expert !== null)
                <section class="grid gap-4 border-y border-paw-line py-7" aria-labelledby="credential-section">
                    <div>
                        <h2 id="credential-section" class="text-xl font-bold">{{ __('ui.private_verification_document_5258949018') }}</h2>
                        <p class="mt-1 text-sm text-paw-muted">{{ __('ui.the_original_file_is_stored_privately_and_never_f4e144d833') }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ __('ui.document_type_5b81f70d59') }}
                            <select name="credential_type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                @foreach ($credential_types as $credentialType => $credentialTypeLabel)
                                    <option value="{{ $credentialType }}" @selected(old('credential_type', 'qualification') === $credentialType)>{{ $credentialTypeLabel }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.document_title_02c6aae53d') }}<input name="credential_title" value="{{ old('credential_title') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.issuer_39e02c46a0') }}<input name="credential_issuer" value="{{ old('credential_issuer') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                    </div>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.pdf_or_image_up_to_10_mb_c6eb813b39') }}
                        <input type="file" name="credential_file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                </section>
            @endunless

            <footer class="flex flex-wrap justify-end gap-2">
                <x-action-control label="{{ __('ui.cancel_19766ed6cc') }}" icon="x" :href="$expert !== null ? route('experts.show', $expert) : route('experts.index')" />
                <button type="submit" class="action action--primary action--comfortable">
                    <x-lucide-save class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $expert !== null ? __('ui.save_profile_0c8209e72e') : __('ui.create_profile_61d30d997d') }}</span>
                </button>
            </footer>
        </form>
    </div>
</x-app-shell>
