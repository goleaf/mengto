<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <x-detail-navigation
                    :href="route('medical-records.show', $medical_record['slug'])"
                    :label="__('presentation.pet_record', ['pet' => $medical_record['pet_name']])"
                />
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.owner_workspace') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.update_health_record') }}</h1>
            </div>
            <x-status-badge label="{{ __('ui.encrypted_private_data') }}" icon="lock-keyhole" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="medical-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_entry_was_not_saved') }}</strong>
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

        @if ($medical_access_url)
            <section class="medical-access-link" aria-labelledby="new-access-link-title">
                <div>
                    <p class="text-xs font-bold uppercase">{{ __('ui.one_time_display') }}</p>
                    <h2 id="new-access-link-title" class="mt-1 font-bold">{{ __('ui.temporary_access_link') }}</h2>
                    <p class="mt-2 break-all text-sm">{{ $medical_access_url }}</p>
                </div>
                <x-ui-icon name="link" size="xl" />
            </section>
        @endif

        <nav class="medical-anchor-nav" aria-label="{{ __('ui.health_record_management') }}">
            <a href="#events"><x-ui-icon name="notebook-pen" size="sm" /> {{ __('ui.event') }}</a>
            <a href="#weight"><x-ui-icon name="scale" size="sm" /> {{ __('ui.weight') }}</a>
            <a href="#vaccinations"><x-ui-icon name="syringe" size="sm" /> {{ __('ui.vaccine') }}</a>
            <a href="#medications"><x-ui-icon name="pill" size="sm" /> {{ __('ui.medication') }}</a>
            <a href="#documents"><x-ui-icon name="file-heart" size="sm" /> {{ __('ui.document') }}</a>
            <a href="#access"><x-ui-icon name="key-round" size="sm" /> {{ __('ui.access') }}</a>
        </nav>

        <div class="medical-manage-grid">
            <div class="grid min-w-0 content-start gap-9">
                <section id="events" class="medical-form-section scroll-mt-28" aria-labelledby="event-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.timeline') }}</p>
                            <h2 id="event-form-title" class="mt-1 text-xl font-bold">{{ __('ui.add_medical_event') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="event">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.event_type') }}
                                <select name="event_type" required>
                                    @forelse ($entry_options['event_types'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_event_types') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.date_and_time') }}
                                <input type="datetime-local" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required>
                            </label>
                            <label>
                                {{ __('ui.title') }}
                                <input name="title" value="{{ old('title') }}" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.status') }}
                                <select name="event_status">
                                    <option value="active">{{ __('ui.active') }}</option>
                                    <option value="suspected">{{ __('ui.suspected') }}</option>
                                    <option value="confirmed">{{ __('ui.confirmed') }}</option>
                                    <option value="controlled">{{ __('ui.controlled') }}</option>
                                    <option value="resolved">{{ __('ui.resolved') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.source') }}
                                <select name="source_type">
                                    <option value="owner">{{ __('ui.owner_observation') }}</option>
                                    <option value="clinic">{{ __('ui.clinic_document') }}</option>
                                    <option value="veterinarian">{{ __('ui.veterinarian') }}</option>
                                    <option value="laboratory">{{ __('ui.laboratory') }}</option>
                                    <option value="import">{{ __('ui.imported') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.source_name') }}
                                <input name="source_name" value="{{ old('source_name') }}" maxlength="160" placeholder="{{ __('ui.clinic_specialist_or_owner') }}">
                            </label>
                            <label>
                                {{ __('ui.severity') }}
                                <select name="severity">
                                    <option value="">{{ __('ui.not_specified') }}</option>
                                    <option value="mild">{{ __('ui.mild') }}</option>
                                    <option value="moderate">{{ __('ui.moderate') }}</option>
                                    <option value="severe">{{ __('ui.severe') }}</option>
                                    <option value="critical">{{ __('ui.critical') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.follow_up') }}
                                <input type="datetime-local" name="follow_up_at" value="{{ old('follow_up_at') }}">
                            </label>
                        </div>
                        <label>
                            {{ __('ui.summary') }}
                            <textarea name="summary" rows="4" maxlength="5000">{{ old('summary') }}</textarea>
                        </label>
                        <label>
                            {{ __('ui.next_step') }}
                            <textarea name="next_step" rows="2" maxlength="1000">{{ old('next_step') }}</textarea>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="is_critical" value="1" @checked(old('is_critical'))>
                            <span>{{ __('ui.include_this_event_in_critical_review') }}</span>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="plus" size="sm" />
                                <span>{{ __('ui.add_event') }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="weight" class="medical-form-section scroll-mt-28" aria-labelledby="weight-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.measurement') }}</p>
                            <h2 id="weight-form-title" class="mt-1 text-xl font-bold">{{ __('ui.record_weight') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="weight">
                        <input type="hidden" name="source_type" value="owner">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.weight') }}
                                <input type="number" name="weight" min="0.001" max="2000" step="0.001" required>
                            </label>
                            <label>
                                {{ __('ui.unit') }}
                                <select name="weight_unit" required>
                                    <option value="kg">{{ __('ui.kilograms') }}</option>
                                    <option value="g">{{ __('ui.grams') }}</option>
                                    <option value="lb">{{ __('ui.pounds') }}</option>
                                    <option value="oz">{{ __('ui.ounces') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.measured_at') }}
                                <input type="datetime-local" name="measured_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </label>
                            <label>
                                {{ __('ui.source') }}
                                <input name="source_name" value="Home scale" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.tare') }}
                                <input type="number" name="tare" min="0" step="0.001">
                            </label>
                            <label>
                                {{ __('ui.conditions') }}
                                <input name="measurement_context" maxlength="120" placeholder="{{ __('ui.morning_before_breakfast') }}">
                            </label>
                        </div>
                        <label>
                            {{ __('ui.note') }}
                            <textarea name="notes" rows="2" maxlength="2000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="scale" size="sm" />
                                <span>{{ __('ui.save_weight') }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="vaccinations" class="medical-form-section scroll-mt-28" aria-labelledby="vaccination-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.preventive_care') }}</p>
                            <h2 id="vaccination-form-title" class="mt-1 text-xl font-bold">{{ __('ui.add_vaccination') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="vaccination">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.vaccine') }}
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.status') }}
                                <select name="vaccination_status" required>
                                    @forelse ($entry_options['vaccination_statuses'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_statuses') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.administered_on') }}
                                <input type="date" name="administered_on" max="{{ now()->toDateString() }}">
                            </label>
                            <label>
                                {{ __('ui.next_due') }}
                                <input type="date" name="next_due_on">
                            </label>
                            <label>
                                {{ __('ui.manufacturer') }}
                                <input name="manufacturer" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.lot_number') }}
                                <input name="lot_number" maxlength="120">
                            </label>
                            <label>
                                {{ __('ui.clinic') }}
                                <input name="source_name" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.veterinarian') }}
                                <input name="professional_name" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.source') }}
                                <select name="source_type">
                                    <option value="owner">{{ __('ui.owner_upload') }}</option>
                                    <option value="clinic">{{ __('ui.clinic') }}</option>
                                    <option value="veterinarian">{{ __('ui.veterinarian') }}</option>
                                    <option value="import">{{ __('ui.imported') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.dose') }}
                                <input name="dose" maxlength="80">
                            </label>
                        </div>
                        <label>
                            {{ __('ui.reaction') }}
                            <textarea name="reaction" rows="2" maxlength="2000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="syringe" size="sm" />
                                <span>{{ __('ui.add_vaccination') }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="medications" class="medical-form-section scroll-mt-28" aria-labelledby="medication-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.prescription_plan') }}</p>
                            <h2 id="medication-form-title" class="mt-1 text-xl font-bold">{{ __('ui.add_medication_course') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="medication">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.medication_name') }}
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.active_ingredient') }}
                                <input name="active_ingredient" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.form') }}
                                <select name="medication_form" required>
                                    <option value="tablet">{{ __('ui.tablet') }}</option>
                                    <option value="capsule">{{ __('ui.capsule') }}</option>
                                    <option value="liquid">{{ __('ui.liquid') }}</option>
                                    <option value="suspension">{{ __('ui.suspension') }}</option>
                                    <option value="drops">{{ __('ui.drops') }}</option>
                                    <option value="ointment">{{ __('ui.ointment') }}</option>
                                    <option value="injection">{{ __('ui.injection_by_specialist') }}</option>
                                    <option value="other">{{ __('ui.other') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.concentration') }}
                                <input name="concentration" maxlength="80">
                            </label>
                            <label>
                                {{ __('ui.exact_prescribed_dose') }}
                                <input name="dose" maxlength="120" required>
                            </label>
                            <label>
                                {{ __('ui.route') }}
                                <input name="route" maxlength="80" required placeholder="{{ __('ui.by_mouth_with_food') }}">
                            </label>
                            <label>
                                {{ __('ui.schedule_type') }}
                                <select name="schedule_type" required>
                                    <option value="fixed">{{ __('ui.fixed_time') }}</option>
                                    <option value="interval">{{ __('ui.interval') }}</option>
                                    <option value="as-needed">{{ __('ui.as_needed') }}</option>
                                    <option value="specific-days">{{ __('ui.specific_days') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.schedule') }}
                                <input name="schedule_text" maxlength="180" required placeholder="{{ __('ui.every_12_hours') }}">
                            </label>
                            <label>
                                {{ __('ui.starts') }}
                                <input type="date" name="starts_on" value="{{ now()->toDateString() }}" required>
                            </label>
                            <label>
                                {{ __('ui.ends') }}
                                <input type="date" name="ends_on">
                            </label>
                            <label>
                                {{ __('ui.next_dose') }}
                                <input type="datetime-local" name="next_dose_at">
                            </label>
                            <label>
                                {{ __('ui.status') }}
                                <select name="medication_status" required>
                                    @forelse ($entry_options['medication_statuses'] as $value => $label)
                                        <option value="{{ $value }}" @selected($value === 'active')>{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_statuses') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.prescribing_specialist') }}
                                <input name="professional_name" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.clinic') }}
                                <input name="source_name" maxlength="160">
                            </label>
                            <label>
                                {{ __('ui.source') }}
                                <select name="source_type">
                                    <option value="owner">{{ __('ui.owner_entry') }}</option>
                                    <option value="veterinarian">{{ __('ui.veterinarian') }}</option>
                                    <option value="clinic">{{ __('ui.clinic') }}</option>
                                    <option value="import">{{ __('ui.imported') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.reason') }}
                                <input name="reason" maxlength="180">
                            </label>
                        </div>
                        <label>
                            {{ __('ui.signed_instructions') }}
                            <textarea name="instructions" rows="4" maxlength="3000"></textarea>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="is_high_risk" value="1">
                            <span>{{ __('ui.require_extra_attention_for_this_medication') }}</span>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="pill" size="sm" />
                                <span>{{ __('ui.add_medication') }}</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section class="medical-form-section" aria-labelledby="reminder-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.care_calendar') }}</p>
                            <h2 id="reminder-form-title" class="mt-1 text-xl font-bold">{{ __('ui.schedule_reminder') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="reminder">
                        <div class="medical-form-grid">
                            <label>
                                {{ __('ui.reminder_type') }}
                                <select name="reminder_type" required>
                                    <option value="vaccination">{{ __('ui.vaccination') }}</option>
                                    <option value="medication">{{ __('ui.medication') }}</option>
                                    <option value="appointment">{{ __('ui.appointment') }}</option>
                                    <option value="follow-up">{{ __('ui.follow_up') }}</option>
                                    <option value="lab-test">{{ __('ui.lab_test') }}</option>
                                    <option value="weight">{{ __('ui.weight') }}</option>
                                    <option value="wound-care">{{ __('ui.wound_care') }}</option>
                                    <option value="rehabilitation">{{ __('ui.rehabilitation') }}</option>
                                    <option value="document">{{ __('ui.document_renewal') }}</option>
                                    <option value="prescription">{{ __('ui.prescription_renewal') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.title') }}
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.due') }}
                                <input type="datetime-local" name="due_at" required>
                            </label>
                            <label>
                                {{ __('ui.priority') }}
                                <select name="priority" required>
                                    <option value="normal">{{ __('ui.normal') }}</option>
                                    <option value="important">{{ __('ui.important') }}</option>
                                    <option value="critical">{{ __('ui.critical') }}</option>
                                    <option value="low">{{ __('ui.low') }}</option>
                                </select>
                            </label>
                        </div>
                        <label>
                            {{ __('ui.instructions') }}
                            <textarea name="instructions" rows="2" maxlength="3000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-ui-icon name="bell-plus" size="sm" />
                                <span>{{ __('ui.schedule') }}</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="grid min-w-0 content-start gap-9">
                <section id="documents" class="medical-form-section scroll-mt-28" aria-labelledby="document-form-title">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.private_storage') }}</p>
                        <h2 id="document-form-title" class="mt-1 text-xl font-bold">{{ __('ui.upload_document') }}</h2>
                    </div>
                    <form method="POST" action="{{ route('medical-records.documents.store', $medical_record['slug']) }}" enctype="multipart/form-data" class="grid gap-4">
                        @csrf
                        <label>
                            {{ __('ui.title') }}
                            <input name="title" maxlength="180" required>
                        </label>
                        <label>
                            {{ __('ui.type') }}
                            <select name="document_type" required>
                                <option value="visit-summary">{{ __('ui.visit_summary') }}</option>
                                <option value="lab-result">{{ __('ui.lab_result') }}</option>
                                <option value="vaccination-certificate">{{ __('ui.vaccination_certificate') }}</option>
                                <option value="prescription">{{ __('ui.prescription') }}</option>
                                <option value="imaging">{{ __('ui.imaging') }}</option>
                                <option value="surgery">{{ __('ui.surgery') }}</option>
                                <option value="insurance">{{ __('ui.insurance') }}</option>
                                <option value="travel">{{ __('ui.travel') }}</option>
                                <option value="invoice">{{ __('ui.invoice') }}</option>
                                <option value="other">{{ __('ui.other') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.source') }}
                            <select name="source_type" required>
                                <option value="owner">{{ __('ui.owner_upload') }}</option>
                                <option value="clinic">{{ __('ui.clinic') }}</option>
                                <option value="veterinarian">{{ __('ui.veterinarian') }}</option>
                                <option value="laboratory">{{ __('ui.laboratory') }}</option>
                                <option value="import">{{ __('ui.imported') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.source_name') }}
                            <input name="source_name" maxlength="160">
                        </label>
                        <label>
                            {{ __('ui.file') }}
                            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.mov,.mp3,.wav" required>
                            <span>{{ __('ui.pdf_image_short_video_or_audio_up_to_20_mb') }}</span>
                        </label>
                        <button class="action action--primary action--compact" type="submit">
                            <x-ui-icon name="upload" size="sm" />
                            <span>{{ __('ui.upload_privately') }}</span>
                        </button>
                    </form>
                </section>

                <section id="access" class="medical-form-section scroll-mt-28" aria-labelledby="access-form-title">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.temporary_access') }}</p>
                        <h2 id="access-form-title" class="mt-1 text-xl font-bold">{{ __('ui.share_selected_sections') }}</h2>
                    </div>
                    <form method="POST" action="{{ route('medical-records.access.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>
                            {{ __('ui.recipient') }}
                            <input name="recipient_name" maxlength="160" required placeholder="{{ __('ui.clinic_or_caregiver_name') }}">
                        </label>
                        <label>
                            {{ __('ui.role') }}
                            <select name="recipient_role" required>
                                <option value="veterinarian">{{ __('ui.veterinarian') }}</option>
                                <option value="clinic">{{ __('ui.clinic') }}</option>
                                <option value="co-owner">{{ __('ui.co_owner') }}</option>
                                <option value="caregiver">{{ __('ui.caregiver') }}</option>
                                <option value="sitter">{{ __('ui.sitter') }}</option>
                                <option value="groomer">{{ __('ui.groomer') }}</option>
                                <option value="rehabilitation-specialist">{{ __('ui.rehabilitation_specialist') }}</option>
                                <option value="shelter">{{ __('ui.shelter') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.access_label') }}
                            <input name="label" maxlength="180" required value="Temporary care review">
                        </label>
                        <fieldset class="grid gap-2">
                            <legend class="text-sm font-bold">{{ __('ui.visible_sections') }}</legend>
                            <div class="grid gap-2">
                                @forelse ($entry_options['share_sections'] as $value => $label)
                                    <label class="medical-check medical-check--boxed">
                                        <input type="checkbox" name="sections[]" value="{{ $value }}" @checked(in_array($value, ['summary', 'medications'], true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-paw-muted">{{ __('ui.no_sections_available') }}</p>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="grid grid-cols-2 gap-3">
                            <label>
                                {{ __('ui.expires_in') }}
                                <select name="expires_in_hours">
                                    <option value="4">{{ __('ui.4_hours') }}</option>
                                    <option value="24" selected>{{ __('ui.1_day') }}</option>
                                    <option value="168">{{ __('ui.7_days') }}</option>
                                    <option value="720">{{ __('ui.30_days') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.view_limit') }}
                                <input type="number" name="max_views" value="5" min="1" max="100" required>
                            </label>
                        </div>
                        <label class="medical-check">
                            <input type="checkbox" name="allow_download" value="1">
                            <span>{{ __('ui.allow_selected_document_downloads') }}</span>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="privacy_acknowledged" value="1" required>
                            <span>{{ __('ui.i_reviewed_the_selected_sections_and_expiry') }}</span>
                        </label>
                        <button class="action action--primary action--compact" type="submit">
                            <x-ui-icon name="link" size="sm" />
                            <span>{{ __('ui.create_access_link') }}</span>
                        </button>
                    </form>
                </section>

                <section class="medical-section" aria-labelledby="active-access-title">
                    <div class="medical-section__heading">
                        <h2 id="active-access-title" class="text-xl font-bold">{{ __('ui.access_grants') }}</h2>
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
                                            <button class="action action--surface action--compact" type="submit" title="{{ __('ui.revoke_access') }}">
                                                <x-ui-icon name="link-2-off" size="sm" />
                                                <span class="sr-only">{{ __('presentation.revoke_access_for', ['name' => $grant['recipient_name']]) }}</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_access_grants') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="access-log-title">
                    <div class="medical-section__heading">
                        <h2 id="access-log-title" class="text-xl font-bold">{{ __('ui.access_history') }}</h2>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($access_logs as $log)
                            <article>
                                <h3 class="font-bold">{{ $log['action'] }}</h3>
                                <p class="mt-1 text-xs text-paw-muted">{{ $log['actor'] }} · {{ $log['role'] }} · {{ $log['time'] }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_shared_link_activity') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
