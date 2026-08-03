<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('medical-records.show', $medical_record['slug']) }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-ui-icon name="arrow-left" size="sm" />
                    {{ __('presentation.pet_record', ['pet' => $medical_record['pet_name']]) }}
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.owner_workspace_cefa8e8061') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.update_health_record_e1f63c72bd') }}</h1>
            </div>
            <x-status-badge label="{{ __('ui.encrypted_private_data_232feb3171') }}" icon="lock-keyhole" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="medical-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_entry_was_not_saved_ebecbcc913') }}</strong>
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

        @if ($medical_access_url)
            <section class="medical-access-link" aria-labelledby="new-access-link-title">
                <div>
                    <p class="text-xs font-bold uppercase">{{ __('ui.one_time_display_d263d31f32') }}</p>
                    <h2 id="new-access-link-title" class="mt-1 font-bold">{{ __('ui.temporary_access_link_f91c3757ef') }}</h2>
                    <p class="mt-2 break-all text-sm">{{ $medical_access_url }}</p>
                </div>
                <x-ui-icon name="link" size="xl" />
            </section>
        @endif

        <nav class="medical-anchor-nav" aria-label="{{ __('ui.health_record_management_d150c7be4e') }}">
            <a href="#events"><x-ui-icon name="notebook-pen" size="sm" /> {{ __('ui.event_4e1f49a9c8') }}</a>
            <a href="#weight"><x-ui-icon name="scale" size="sm" /> {{ __('ui.weight_81d27ef6d5') }}</a>
            <a href="#vaccinations"><x-ui-icon name="syringe" size="sm" /> {{ __('ui.vaccine_8c707f7772') }}</a>
            <a href="#medications"><x-ui-icon name="pill" size="sm" /> {{ __('ui.medication_00c96546af') }}</a>
            <a href="#documents"><x-ui-icon name="file-heart" size="sm" /> {{ __('ui.document_d6bd8c0aee') }}</a>
            <a href="#access"><x-ui-icon name="key-round" size="sm" /> {{ __('ui.access_ec5ba0abb7') }}</a>
        </nav>

        <div class="medical-manage-grid">
            <div class="grid min-w-0 content-start gap-9">
                <section id="events" class="medical-form-section scroll-mt-28" aria-labelledby="event-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.timeline_9dcff98e27') }}</p>
                            <h2 id="event-form-title" class="mt-1 text-xl font-bold">{{ __('ui.add_medical_event_6e5bafd156') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="event">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.event_type_70d8170359') }}
                                <select name="event_type" required>
                                    @forelse ($entry_options['event_types'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_event_types_ea0c3aa8c2') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.date_and_time_a079c3bbe2') }}
                                <input type="datetime-local" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required>
                            </label>
                            <label>
                                {{ __('ui.title_7e8cd2056d') }}
                                <input name="title" value="{{ old('title') }}" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.status_920e413c7d') }}
                                <select name="event_status">
                                    <option value="active">{{ __('ui.active_9234069589') }}</option>
                                    <option value="suspected">{{ __('ui.suspected_7d089c936e') }}</option>
                                    <option value="confirmed">{{ __('ui.confirmed_fe00b67b6d') }}</option>
                                    <option value="controlled">{{ __('ui.controlled_7d883fbb5e') }}</option>
                                    <option value="resolved">{{ __('ui.resolved_5be3c2c835') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.source_0e570ca6fa') }}
                                <select name="source_type">
                                    <option value="owner">{{ __('ui.owner_observation_f4562a4f6c') }}</option>
                                    <option value="clinic">{{ __('ui.clinic_document_aa88f7ee39') }}</option>
                                    <option value="veterinarian">{{ __('ui.veterinarian_38d6a38c0c') }}</option>
                                    <option value="laboratory">{{ __('ui.laboratory_5e37d1bb9d') }}</option>
                                    <option value="import">{{ __('ui.imported_321f179c80') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.source_name_1939968f47') }}
                                <input name="source_name" value="{{ old('source_name') }}" maxlength="160" placeholder="{{ __('ui.clinic_specialist_or_owner_2570d60d22') }}">
                            </label>
                            <label>
                                {{ __('ui.severity_5e9f98120d') }}
                                <select name="severity">
                                    <option value="">{{ __('ui.not_specified_dc12bec5d7') }}</option>
                                    <option value="mild">{{ __('ui.mild_329d817227') }}</option>
                                    <option value="moderate">{{ __('ui.moderate_5c42afc7a2') }}</option>
                                    <option value="severe">{{ __('ui.severe_aa93f64a30') }}</option>
                                    <option value="critical">{{ __('ui.critical_427dd2969b') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.follow_up_09b2d9cd11') }}
                                <input type="datetime-local" name="follow_up_at" value="{{ old('follow_up_at') }}">
                            </label>
                        </div>
                        <label>
                            {{ __('ui.summary_8e76a94ac8') }}
                            <textarea name="summary" rows="4" maxlength="5000">{{ old('summary') }}</textarea>
                        </label>
                        <label>
                            {{ __('ui.next_step_298a9207a7') }}
                            <textarea name="next_step" rows="2" maxlength="1000">{{ old('next_step') }}</textarea>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="is_critical" value="1" @checked(old('is_critical'))>
                            <span>{{ __('ui.include_this_event_in_critical_review_e2ec764a29') }}</span>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="plus" size="sm" />
                                <span>{{ __('ui.add_event_57d208bc0d') }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="weight" class="medical-form-section scroll-mt-28" aria-labelledby="weight-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.measurement_385165e2ff') }}</p>
                            <h2 id="weight-form-title" class="mt-1 text-xl font-bold">{{ __('ui.record_weight_a93f30d64e') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="weight">
                        <input type="hidden" name="source_type" value="owner">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.weight_81d27ef6d5') }}
                                <input type="number" name="weight" min="0.001" max="2000" step="0.001" required>
                            </label>
                            <label>
                                {{ __('ui.unit_4e545960f1') }}
                                <select name="weight_unit" required>
                                    <option value="kg">{{ __('ui.kilograms_1bb53a5e14') }}</option>
                                    <option value="g">{{ __('ui.grams_0d50efff06') }}</option>
                                    <option value="lb">{{ __('ui.pounds_b48176ac41') }}</option>
                                    <option value="oz">{{ __('ui.ounces_adf03b72d3') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.measured_at_e3eb2afdf9') }}
                                <input type="datetime-local" name="measured_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </label>
                            <label>
                                {{ __('ui.source_0e570ca6fa') }}
                                <input name="source_name" value="Home scale" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.tare_9962ec9309') }}
                                <input type="number" name="tare" min="0" step="0.001">
                            </label>
                            <label>
                                {{ __('ui.conditions_97d4be8960') }}
                                <input name="measurement_context" maxlength="120" placeholder="{{ __('ui.morning_before_breakfast_7e1e4f2dbb') }}">
                            </label>
                        </div>
                        <label>
                            {{ __('ui.note_d8da2c49df') }}
                            <textarea name="notes" rows="2" maxlength="2000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="scale" size="sm" />
                                <span>{{ __('ui.save_weight_638c50fe24') }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="vaccinations" class="medical-form-section scroll-mt-28" aria-labelledby="vaccination-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.preventive_care_e964c75227') }}</p>
                            <h2 id="vaccination-form-title" class="mt-1 text-xl font-bold">{{ __('ui.add_vaccination_80ced041ed') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="vaccination">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.vaccine_8c707f7772') }}
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.status_920e413c7d') }}
                                <select name="vaccination_status" required>
                                    @forelse ($entry_options['vaccination_statuses'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_statuses_b34efdc994') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.administered_on_70a46ae7ca') }}
                                <input type="date" name="administered_on" max="{{ now()->toDateString() }}">
                            </label>
                            <label>
                                {{ __('ui.next_due_98440480a7') }}
                                <input type="date" name="next_due_on">
                            </label>
                            <label>
                                {{ __('ui.manufacturer_1af384c577') }}
                                <input name="manufacturer" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.lot_number_b009f5d0b3') }}
                                <input name="lot_number" maxlength="120">
                            </label>
                            <label>
                                {{ __('ui.clinic_0a8df495f7') }}
                                <input name="source_name" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.veterinarian_38d6a38c0c') }}
                                <input name="professional_name" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.source_0e570ca6fa') }}
                                <select name="source_type">
                                    <option value="owner">{{ __('ui.owner_upload_07aae94573') }}</option>
                                    <option value="clinic">{{ __('ui.clinic_0a8df495f7') }}</option>
                                    <option value="veterinarian">{{ __('ui.veterinarian_38d6a38c0c') }}</option>
                                    <option value="import">{{ __('ui.imported_321f179c80') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.dose_6b942766c4') }}
                                <input name="dose" maxlength="80">
                            </label>
                        </div>
                        <label>
                            {{ __('ui.reaction_d8be728bfd') }}
                            <textarea name="reaction" rows="2" maxlength="2000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="syringe" size="sm" />
                                <span>{{ __('ui.add_vaccination_80ced041ed') }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="medications" class="medical-form-section scroll-mt-28" aria-labelledby="medication-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.prescription_plan_2ae6b54df8') }}</p>
                            <h2 id="medication-form-title" class="mt-1 text-xl font-bold">{{ __('ui.add_medication_course_9403e6291f') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="medication">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.medication_name_376f8b8403') }}
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.active_ingredient_7a68c91eda') }}
                                <input name="active_ingredient" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.form_2e0e960ab3') }}
                                <select name="medication_form" required>
                                    <option value="tablet">{{ __('ui.tablet_e34a879c8b') }}</option>
                                    <option value="capsule">{{ __('ui.capsule_51ee757a7b') }}</option>
                                    <option value="liquid">{{ __('ui.liquid_9a83788b35') }}</option>
                                    <option value="suspension">{{ __('ui.suspension_acf82993e1') }}</option>
                                    <option value="drops">{{ __('ui.drops_3701544139') }}</option>
                                    <option value="ointment">{{ __('ui.ointment_d34c39bea7') }}</option>
                                    <option value="injection">{{ __('ui.injection_by_specialist_ba79209fb7') }}</option>
                                    <option value="other">{{ __('ui.other_f97e9da0e3') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.concentration_c2cfec6662') }}
                                <input name="concentration" maxlength="80">
                            </label>
                            <label>
                                {{ __('ui.exact_prescribed_dose_dd21053c06') }}
                                <input name="dose" maxlength="120" required>
                            </label>
                            <label>
                                {{ __('ui.route_adc74704d6') }}
                                <input name="route" maxlength="80" required placeholder="{{ __('ui.by_mouth_with_food_976d1219d9') }}">
                            </label>
                            <label>
                                {{ __('ui.schedule_type_e628819cbc') }}
                                <select name="schedule_type" required>
                                    <option value="fixed">{{ __('ui.fixed_time_12de5fc659') }}</option>
                                    <option value="interval">{{ __('ui.interval_6f45b0005e') }}</option>
                                    <option value="as-needed">{{ __('ui.as_needed_eb5a88cac9') }}</option>
                                    <option value="specific-days">{{ __('ui.specific_days_4accd1ecf2') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.schedule_f4830a1dae') }}
                                <input name="schedule_text" maxlength="180" required placeholder="{{ __('ui.every_12_hours_9a289972dd') }}">
                            </label>
                            <label>
                                {{ __('ui.starts_96dbedeca7') }}
                                <input type="date" name="starts_on" value="{{ now()->toDateString() }}" required>
                            </label>
                            <label>
                                {{ __('ui.ends_e98982c9f2') }}
                                <input type="date" name="ends_on">
                            </label>
                            <label>
                                {{ __('ui.next_dose_fa151dd45c') }}
                                <input type="datetime-local" name="next_dose_at">
                            </label>
                            <label>
                                {{ __('ui.status_920e413c7d') }}
                                <select name="medication_status" required>
                                    @forelse ($entry_options['medication_statuses'] as $value => $label)
                                        <option value="{{ $value }}" @selected($value === 'active')>{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_statuses_b34efdc994') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.prescribing_specialist_cea8db3329') }}
                                <input name="professional_name" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.clinic_0a8df495f7') }}
                                <input name="source_name" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.source_0e570ca6fa') }}
                                <select name="source_type">
                                    <option value="owner">{{ __('ui.owner_entry_7735568761') }}</option>
                                    <option value="veterinarian">{{ __('ui.veterinarian_38d6a38c0c') }}</option>
                                    <option value="clinic">{{ __('ui.clinic_0a8df495f7') }}</option>
                                    <option value="import">{{ __('ui.imported_321f179c80') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.reason_f81ab834de') }}
                                <input name="reason" maxlength="180">
                            </label>
                        </div>
                        <label>
                            {{ __('ui.signed_instructions_19ca6d0e74') }}
                            <textarea name="instructions" rows="4" maxlength="3000"></textarea>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="is_high_risk" value="1">
                            <span>{{ __('ui.require_extra_attention_for_this_medication_63fb1d6097') }}</span>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="pill" size="sm" />
                                <span>{{ __('ui.add_medication_50f2705d69') }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section class="medical-form-section" aria-labelledby="reminder-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.care_calendar_02f69ec0b2') }}</p>
                            <h2 id="reminder-form-title" class="mt-1 text-xl font-bold">{{ __('ui.schedule_reminder_413101284c') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="reminder">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.reminder_type_9e362c9e1f') }}
                                <select name="reminder_type" required>
                                    <option value="vaccination">{{ __('ui.vaccination_cf22c908f3') }}</option>
                                    <option value="medication">{{ __('ui.medication_00c96546af') }}</option>
                                    <option value="appointment">{{ __('ui.appointment_8a4f957e08') }}</option>
                                    <option value="follow-up">{{ __('ui.follow_up_09b2d9cd11') }}</option>
                                    <option value="lab-test">{{ __('ui.lab_test_656145566e') }}</option>
                                    <option value="weight">{{ __('ui.weight_81d27ef6d5') }}</option>
                                    <option value="wound-care">{{ __('ui.wound_care_3d0219f117') }}</option>
                                    <option value="rehabilitation">{{ __('ui.rehabilitation_970fd6aa32') }}</option>
                                    <option value="document">{{ __('ui.document_renewal_ff58eba78b') }}</option>
                                    <option value="prescription">{{ __('ui.prescription_renewal_a94e4a02a8') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.title_7e8cd2056d') }}
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.due_9071738f14') }}
                                <input type="datetime-local" name="due_at" required>
                            </label>
                            <label>
                                {{ __('ui.priority_d60dbba079') }}
                                <select name="priority" required>
                                    <option value="normal">{{ __('ui.normal_a7248eeb45') }}</option>
                                    <option value="important">{{ __('ui.important_ddca9a57e6') }}</option>
                                    <option value="critical">{{ __('ui.critical_427dd2969b') }}</option>
                                    <option value="low">{{ __('ui.low_f793de205e') }}</option>
                                </select>
                            </label>
                        </div>
                        <label>
                            {{ __('ui.instructions_934652dce4') }}
                            <textarea name="instructions" rows="2" maxlength="3000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="bell-plus" size="sm" />
                                <span>{{ __('ui.schedule_f4830a1dae') }}</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="grid min-w-0 content-start gap-9">
                <section id="documents" class="medical-form-section scroll-mt-28" aria-labelledby="document-form-title">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.private_storage_c7a8134741') }}</p>
                        <h2 id="document-form-title" class="mt-1 text-xl font-bold">{{ __('ui.upload_document_507ae671a6') }}</h2>
                    </div>
                    <form method="POST" action="{{ route('medical-records.documents.store', $medical_record['slug']) }}" enctype="multipart/form-data" class="grid gap-4">
                        @csrf
                        <label>
                            {{ __('ui.title_7e8cd2056d') }}
                            <input name="title" maxlength="180" required>
                        </label>
                        <label>
                            {{ __('ui.type_baaddf70fb') }}
                            <select name="document_type" required>
                                <option value="visit-summary">{{ __('ui.visit_summary_afb2890572') }}</option>
                                <option value="lab-result">{{ __('ui.lab_result_9884db6095') }}</option>
                                <option value="vaccination-certificate">{{ __('ui.vaccination_certificate_c2733945db') }}</option>
                                <option value="prescription">{{ __('ui.prescription_9bc867e65b') }}</option>
                                <option value="imaging">{{ __('ui.imaging_60afdf5ecd') }}</option>
                                <option value="surgery">{{ __('ui.surgery_022eaabcc6') }}</option>
                                <option value="insurance">{{ __('ui.insurance_4594b73756') }}</option>
                                <option value="travel">{{ __('ui.travel_d2b98fb537') }}</option>
                                <option value="invoice">{{ __('ui.invoice_b832002da2') }}</option>
                                <option value="other">{{ __('ui.other_f97e9da0e3') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.source_0e570ca6fa') }}
                            <select name="source_type" required>
                                <option value="owner">{{ __('ui.owner_upload_07aae94573') }}</option>
                                <option value="clinic">{{ __('ui.clinic_0a8df495f7') }}</option>
                                <option value="veterinarian">{{ __('ui.veterinarian_38d6a38c0c') }}</option>
                                <option value="laboratory">{{ __('ui.laboratory_5e37d1bb9d') }}</option>
                                <option value="import">{{ __('ui.imported_321f179c80') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.source_name_1939968f47') }}
                            <input name="source_name" maxlength="160">
                        </label>
                        <label>
                            {{ __('ui.file_50009ce1da') }}
                            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.mov,.mp3,.wav" required>
                            <span>{{ __('ui.pdf_image_short_video_or_audio_up_to_30b8b25a60') }}</span>
                        </label>
                        <button class="action action--primary action--compact" type="submit">
                            <x-ui-icon name="upload" size="sm" />
                            <span>{{ __('ui.upload_privately_b34a128b67') }}</span>
                        </button>
                    </form>
                </section>

                <section id="access" class="medical-form-section scroll-mt-28" aria-labelledby="access-form-title">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.temporary_access_7059688673') }}</p>
                        <h2 id="access-form-title" class="mt-1 text-xl font-bold">{{ __('ui.share_selected_sections_c03f47602c') }}</h2>
                    </div>
                    <form method="POST" action="{{ route('medical-records.access.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>
                            {{ __('ui.recipient_51fac985e9') }}
                            <input name="recipient_name" maxlength="160" required placeholder="{{ __('ui.clinic_or_caregiver_name_6f876d01ad') }}">
                        </label>
                        <label>
                            {{ __('ui.role_14736a2eb9') }}
                            <select name="recipient_role" required>
                                <option value="veterinarian">{{ __('ui.veterinarian_38d6a38c0c') }}</option>
                                <option value="clinic">{{ __('ui.clinic_0a8df495f7') }}</option>
                                <option value="co-owner">{{ __('ui.co_owner_f3027e079c') }}</option>
                                <option value="caregiver">{{ __('ui.caregiver_b45607f244') }}</option>
                                <option value="sitter">{{ __('ui.sitter_d26540f1d7') }}</option>
                                <option value="groomer">{{ __('ui.groomer_1f4df5ea23') }}</option>
                                <option value="rehabilitation-specialist">{{ __('ui.rehabilitation_specialist_8077210adf') }}</option>
                                <option value="shelter">{{ __('ui.shelter_cfcd1f3d6a') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.access_label_14b6448467') }}
                            <input name="label" maxlength="180" required value="Temporary care review">
                        </label>
                        <fieldset class="grid gap-2">
                            <legend class="text-sm font-bold">{{ __('ui.visible_sections_da3cd28fb0') }}</legend>
                            <div class="grid gap-2">
                                @forelse ($entry_options['share_sections'] as $value => $label)
                                    <label class="medical-check medical-check--boxed">
                                        <input type="checkbox" name="sections[]" value="{{ $value }}" @checked(in_array($value, ['summary', 'medications'], true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-paw-muted">{{ __('ui.no_sections_available_32dd37d020') }}</p>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="grid grid-cols-2 gap-3">
                            <label>
                                {{ __('ui.expires_in_de9967524f') }}
                                <select name="expires_in_hours">
                                    <option value="4">{{ __('ui.4_hours_e5bc992788') }}</option>
                                    <option value="24" selected>{{ __('ui.1_day_fa665d95d2') }}</option>
                                    <option value="168">{{ __('ui.7_days_7f920bb639') }}</option>
                                    <option value="720">{{ __('ui.30_days_ffd7280513') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.view_limit_f7d2beba7f') }}
                                <input type="number" name="max_views" value="5" min="1" max="100" required>
                            </label>
                        </div>
                        <label class="medical-check">
                            <input type="checkbox" name="allow_download" value="1">
                            <span>{{ __('ui.allow_selected_document_downloads_f6ee3bc8c8') }}</span>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="privacy_acknowledged" value="1" required>
                            <span>{{ __('ui.i_reviewed_the_selected_sections_and_expiry_720b3706bc') }}</span>
                        </label>
                        <button class="action action--primary action--compact" type="submit">
                            <x-ui-icon name="link" size="sm" />
                            <span>{{ __('ui.create_access_link_281b440b33') }}</span>
                        </button>
                    </form>
                </section>

                <section class="medical-section" aria-labelledby="active-access-title">
                    <div class="medical-section__heading">
                        <h2 id="active-access-title" class="text-xl font-bold">{{ __('ui.access_grants_2fd169b4b3') }}</h2>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold">{{ $grant['recipient_name'] }}</h3>
                                        <p class="mt-1 text-xs text-paw-muted">{{ $grant['recipient_role'] }} · {{ $grant['status'] }}</p>
                                        <p class="mt-2 text-xs text-paw-muted">{{ implode(', ', $grant['sections']) }}</p>
                                        <p class="mt-1 text-xs font-semibold">{{ __('presentation.grant_expires_views', ['expires' => $grant['expires_at'], 'views' => $grant['views']]) }}</p>
                                    </div>
                                    @if ($grant['active'])
                                        <form method="POST" action="{{ $grant['revoke_url'] }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="action action--surface action--compact" type="submit" title="{{ __('ui.revoke_access_ab292ddb87') }}">
                                                <x-ui-icon name="link-2-off" size="sm" />
                                                <span class="sr-only">{{ __('presentation.revoke_access_for', ['name' => $grant['recipient_name']]) }}</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_access_grants_dfb14eac50') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="access-log-title">
                    <div class="medical-section__heading">
                        <h2 id="access-log-title" class="text-xl font-bold">{{ __('ui.access_history_29e9d0964e') }}</h2>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($access_logs as $log)
                            <article>
                                <h3 class="font-bold">{{ $log['action'] }}</h3>
                                <p class="mt-1 text-xs text-paw-muted">{{ $log['actor'] }} · {{ $log['role'] }} · {{ $log['time'] }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_shared_link_activity_ff9df100dd') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
