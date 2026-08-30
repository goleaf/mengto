<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full min-w-0 max-w-5xl gap-6">
        <x-page-header
            :eyebrow="$expert['name']"
            :title="__('ui.request_a_consultation')"
            :description="$expert['type'].' · '.$expert['city'].' · '.implode(', ', $expert['formats'])"
            heading-id="expert-booking-heading"
            :action-label="__('ui.back_to_profile')"
            action-icon="arrow-left"
            :action-href="route('experts.show', $expert['slug'])"
            action-variant="paper"
            data-section="expert-booking-header"
        />

        <section class="flex gap-3 border-l-4 border-red-500 bg-red-50 p-4 text-red-950" aria-label="{{ __('ui.emergency_warning') }}">
            <x-ui-icon name="siren" size="lg" class="mt-0.5 shrink-0" />
            <div>
                <h2 class="font-bold">{{ __('ui.do_not_use_planned_booking_for_an_emergency') }}</h2>
                <p class="mt-1 text-sm">{{ __('ui.breathing_difficulty_collapse_seizures_severe_bleeding_poisoning_inability_to_urinate_major_trauma_overheating_or_severe_weakness_require_immediate_contact') }}</p>
            </div>
        </section>

        @if ($errors->any())
            <section class="rounded-md border border-red-300 bg-red-50 p-4 text-red-950" role="alert">
                <h2 class="font-bold">{{ __('ui.the_appointment_was_not_submitted') }}</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>{{ __('ui.review_the_form_and_try_again') }}</li>
                    @endforelse
                </ul>
            </section>
        @endif

        <form method="POST" action="{{ route('experts.bookings.store', $expert['slug']) }}" enctype="multipart/form-data" class="grid w-full min-w-0 gap-8">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotency_key) }}">

            <section class="min-w-0" aria-labelledby="service-selection">
                <h2 id="service-selection" class="text-xl font-bold">{{ __('ui.1_choose_a_service') }}</h2>
                <div class="mt-4 grid min-w-0 gap-3 md:grid-cols-2">
                    @forelse ($services as $service)
                        <label class="grid min-w-0 cursor-pointer gap-2 rounded-md border border-paw-line bg-white p-4 has-[:checked]:border-paw-leaf has-[:checked]:ring-2 has-[:checked]:ring-paw-leaf/20">
                            <span class="flex min-w-0 items-start gap-3">
                                <input type="radio" name="service_id" value="{{ $service['id'] }}" required @checked((string) old('service_id', $selected_service_id) === (string) $service['id']) class="mt-1 size-4 text-paw-leaf">
                                <span class="min-w-0 flex-1">
                                    <strong class="block">{{ $service['name'] }}</strong>
                                    <span class="mt-1 block text-sm text-paw-muted">{{ $service['format'] }} · {{ $service['duration'] }} · {{ $service['price'] !== null ? $service['currency'].' '.$service['price'] : __('ui.price_on_request') }}</span>
                                </span>
                            </span>
                            <span class="text-sm leading-6">{{ $service['description'] }}</span>
                            <span class="text-xs text-paw-muted">{{ $service['cancellation_policy'] }}</span>
                        </label>
                    @empty
                        <p class="text-paw-muted">{{ __('ui.no_active_services_are_available') }}</p>
                    @endforelse
                </div>
            </section>

            <section class="grid min-w-0 gap-4 border-y border-paw-line py-7" aria-labelledby="time-selection">
                <h2 id="time-selection" class="text-xl font-bold">{{ __('ui.2_select_pet_and_time') }}</h2>
                <div class="grid min-w-0 gap-4 sm:grid-cols-2">
                    <label class="grid min-w-0 gap-1 text-sm font-semibold">
                        {{ __('ui.pet') }}
                        <select name="pet_key" required class="w-full min-w-0 max-w-full rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($pets as $key => $pet)
                                <option value="{{ $key }}" @selected(old('pet_key') === $key)>{{ $pet['name'] }} · {{ $pet['species_label'] }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_pets_available') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid min-w-0 gap-1 text-sm font-semibold">
                        {{ __('ui.available_time') }}
                        <select name="availability_slot_id" required class="w-full min-w-0 max-w-full rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">{{ __('ui.choose_an_opening') }}</option>
                            @forelse ($slots as $slot)
                                <option value="{{ $slot['id'] }}" @selected((string) old('availability_slot_id') === (string) $slot['id'])>
                                    {{ $slot['label'] }}–{{ $slot['ends_at'] }} · {{ $slot['format'] }}
                                    @if ($slot['location']) · {{ $slot['location'] }} @endif
                                    · {{ __('ui.available_places_count', ['count' => $slot['remaining']]) }}
                                </option>
                            @empty
                                <option disabled>{{ __('ui.no_open_time_slots') }}</option>
                            @endforelse
                        </select>
                    </label>
                </div>
            </section>

            <section class="grid min-w-0 gap-4" aria-labelledby="consultation-context">
                <div>
                    <h2 id="consultation-context" class="text-xl font-bold">{{ __('ui.3_consultation_context') }}</h2>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('ui.share_only_information_needed_for_this_appointment_the_form_does_not_diagnose_the_pet') }}</p>
                </div>
                <label class="grid min-w-0 gap-1 text-sm font-semibold">
                    {{ __('ui.main_question') }}
                    <textarea name="main_question" required minlength="20" rows="5" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.describe_what_you_need_help_with_and_the_most_important_context') }}">{{ old('main_question') }}</textarea>
                </label>
                <div class="grid min-w-0 gap-4 sm:grid-cols-2">
                    <label class="grid min-w-0 gap-1 text-sm font-semibold">{{ __('ui.when_it_started') }}<textarea name="started_at" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('started_at') }}</textarea></label>
                    <label class="grid min-w-0 gap-1 text-sm font-semibold">{{ __('ui.what_you_already_tried') }}<textarea name="tried" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('tried') }}</textarea></label>
                    <label class="grid min-w-0 gap-1 text-sm font-semibold">{{ __('ui.previous_professional_input') }}<textarea name="previous_professional" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('previous_professional') }}</textarea></label>
                    <label class="grid min-w-0 gap-1 text-sm font-semibold">{{ __('ui.desired_result') }}<textarea name="desired_result" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('desired_result') }}</textarea></label>
                </div>
                <label class="grid min-w-0 gap-1 text-sm font-semibold">{{ __('ui.communication_or_accessibility_needs') }}<textarea name="access_needs" rows="2" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('access_needs') }}</textarea></label>
                <input type="hidden" name="urgent_signs" value="0">
                <label class="flex items-start gap-2 rounded-md border border-red-200 bg-red-50 p-3 text-sm">
                    <input type="checkbox" name="urgent_signs" value="1" @checked(old('urgent_signs')) class="mt-0.5 size-4">
                    <span><strong>{{ __('ui.there_are_urgent_warning_signs') }}</strong> {{ __('ui.selecting_this_stops_planned_booking_and_directs_you_to_immediate_veterinary_help') }}</span>
                </label>
            </section>

            <section class="grid min-w-0 gap-4 border-y border-paw-line py-7" aria-labelledby="document-sharing">
                <div>
                    <h2 id="document-sharing" class="text-xl font-bold">{{ __('ui.4_optional_temporary_document_access') }}</h2>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('ui.the_selected_specialist_receives_access_only_to_this_file_you_can_revoke_it_later_from_the_appointment_page') }}</p>
                </div>
                <div class="grid min-w-0 gap-4 sm:grid-cols-2">
                    <label class="grid min-w-0 gap-1 text-sm font-semibold">{{ __('ui.document_label') }}<input name="document_label" value="{{ old('document_label') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.blood_test_from_july_20') }}"></label>
                    <label class="grid min-w-0 gap-1 text-sm font-semibold">{{ __('ui.document_type') }}<input name="document_type" value="{{ old('document_type') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.lab_result_hyphenated') }}"></label>
                </div>
                <label class="grid min-w-0 gap-1 text-sm font-semibold">
                    {{ __('ui.pdf_or_image_up_to_10_mb') }}
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full min-w-0 max-w-full rounded-md border border-paw-line bg-white px-3 py-2.5">
                </label>
            </section>

            <section class="grid min-w-0 gap-3" aria-labelledby="booking-consent">
                <h2 id="booking-consent" class="text-xl font-bold">{{ __('ui.5_confirm_boundaries_and_consent') }}</h2>
                <label class="flex min-h-11 items-start gap-2 text-sm"><input type="checkbox" name="terms_accepted" value="1" required class="mt-0.5 size-4"><span>{{ __('ui.i_accept_the_displayed_service_scope_price_preparation_and_cancellation_policy') }}</span></label>
                <label class="flex min-h-11 items-start gap-2 text-sm"><input type="checkbox" name="data_consent" value="1" required class="mt-0.5 size-4"><span>{{ __('ui.i_authorize_the_minimum_pet_and_contact_data_needed_to_manage_this_appointment') }}</span></label>
                <input type="hidden" name="recording_consent" value="0">
                <label class="flex min-h-11 items-start gap-2 text-sm"><input type="checkbox" name="recording_consent" value="1" class="mt-0.5 size-4"><span>{{ __('ui.i_agree_to_a_consultation_recording_if_the_specialist_requests_it_recording_cannot_start_without_visible_confirmation') }}</span></label>
            </section>

            <footer class="flex flex-wrap justify-end gap-2">
                <x-action-control label="{{ __('ui.cancel') }}" icon="x" :href="route('experts.show', $expert['slug'])" />
                <button type="submit" class="action action--primary action--comfortable" @disabled($services === [] || $slots === [])>
                    <x-ui-icon name="calendar-check" size="sm" />
                    <span>{{ __('ui.submit_appointment_request') }}</span>
                </button>
            </footer>
        </form>
    </div>
</x-app-shell>
