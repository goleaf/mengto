<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('medical-records.show', $medical_record['slug']) }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                    {{ $medical_record['pet_name'] }}'s record
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">Owner workspace</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Update health record</h1>
            </div>
            <x-status-badge label="Encrypted private data" icon="lock-keyhole" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="medical-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>The entry was not saved</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>Validation failed.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        @if ($medical_access_url)
            <section class="medical-access-link" aria-labelledby="new-access-link-title">
                <div>
                    <p class="text-xs font-bold uppercase">One-time display</p>
                    <h2 id="new-access-link-title" class="mt-1 font-bold">Temporary access link</h2>
                    <p class="mt-2 break-all text-sm">{{ $medical_access_url }}</p>
                </div>
                <x-lucide-link class="size-6" aria-hidden="true" />
            </section>
        @endif

        <nav class="medical-anchor-nav" aria-label="Health record management">
            <a href="#events"><x-lucide-notebook-pen class="size-4" aria-hidden="true" /> Event</a>
            <a href="#weight"><x-lucide-scale class="size-4" aria-hidden="true" /> Weight</a>
            <a href="#vaccinations"><x-lucide-syringe class="size-4" aria-hidden="true" /> Vaccine</a>
            <a href="#medications"><x-lucide-pill class="size-4" aria-hidden="true" /> Medication</a>
            <a href="#documents"><x-lucide-file-heart class="size-4" aria-hidden="true" /> Document</a>
            <a href="#access"><x-lucide-key-round class="size-4" aria-hidden="true" /> Access</a>
        </nav>

        <div class="medical-manage-grid">
            <div class="grid min-w-0 content-start gap-9">
                <section id="events" class="medical-form-section scroll-mt-28" aria-labelledby="event-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Timeline</p>
                            <h2 id="event-form-title" class="mt-1 text-xl font-bold">Add medical event</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="event">
                        <div class="medical-form-grid">
                            <label>
                                Event type
                                <select name="event_type" required>
                                    @forelse ($entry_options['event_types'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>No event types</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                Date and time
                                <input type="datetime-local" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required>
                            </label>
                            <label>
                                Title
                                <input name="title" value="{{ old('title') }}" maxlength="180" required>
                            </label>
                            <label>
                                Status
                                <select name="event_status">
                                    <option value="active">Active</option>
                                    <option value="suspected">Suspected</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="controlled">Controlled</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                            </label>
                            <label>
                                Source
                                <select name="source_type">
                                    <option value="owner">Owner observation</option>
                                    <option value="clinic">Clinic document</option>
                                    <option value="veterinarian">Veterinarian</option>
                                    <option value="laboratory">Laboratory</option>
                                    <option value="import">Imported</option>
                                </select>
                            </label>
                            <label>
                                Source name
                                <input name="source_name" value="{{ old('source_name') }}" maxlength="160" placeholder="Clinic, specialist, or owner">
                            </label>
                            <label>
                                Severity
                                <select name="severity">
                                    <option value="">Not specified</option>
                                    <option value="mild">Mild</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="severe">Severe</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </label>
                            <label>
                                Follow-up
                                <input type="datetime-local" name="follow_up_at" value="{{ old('follow_up_at') }}">
                            </label>
                        </div>
                        <label>
                            Summary
                            <textarea name="summary" rows="4" maxlength="5000">{{ old('summary') }}</textarea>
                        </label>
                        <label>
                            Next step
                            <textarea name="next_step" rows="2" maxlength="1000">{{ old('next_step') }}</textarea>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="is_critical" value="1" @checked(old('is_critical'))>
                            <span>Include this event in critical review</span>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-lucide-plus class="icon icon--sm" aria-hidden="true" />
                                <span>Add event</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="weight" class="medical-form-section scroll-mt-28" aria-labelledby="weight-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Measurement</p>
                            <h2 id="weight-form-title" class="mt-1 text-xl font-bold">Record weight</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="weight">
                        <input type="hidden" name="source_type" value="owner">
                        <div class="medical-form-grid">
                            <label>
                                Weight
                                <input type="number" name="weight" min="0.001" max="2000" step="0.001" required>
                            </label>
                            <label>
                                Unit
                                <select name="weight_unit" required>
                                    <option value="kg">Kilograms</option>
                                    <option value="g">Grams</option>
                                    <option value="lb">Pounds</option>
                                    <option value="oz">Ounces</option>
                                </select>
                            </label>
                            <label>
                                Measured at
                                <input type="datetime-local" name="measured_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </label>
                            <label>
                                Source
                                <input name="source_name" value="Home scale" maxlength="160">
                            </label>
                            <label>
                                Tare
                                <input type="number" name="tare" min="0" step="0.001">
                            </label>
                            <label>
                                Conditions
                                <input name="measurement_context" maxlength="120" placeholder="Morning before breakfast">
                            </label>
                        </div>
                        <label>
                            Note
                            <textarea name="notes" rows="2" maxlength="2000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-lucide-scale class="icon icon--sm" aria-hidden="true" />
                                <span>Save weight</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="vaccinations" class="medical-form-section scroll-mt-28" aria-labelledby="vaccination-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Preventive care</p>
                            <h2 id="vaccination-form-title" class="mt-1 text-xl font-bold">Add vaccination</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="vaccination">
                        <div class="medical-form-grid">
                            <label>
                                Vaccine
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                Status
                                <select name="vaccination_status" required>
                                    @forelse ($entry_options['vaccination_statuses'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>No statuses</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                Administered on
                                <input type="date" name="administered_on" max="{{ now()->toDateString() }}">
                            </label>
                            <label>
                                Next due
                                <input type="date" name="next_due_on">
                            </label>
                            <label>
                                Manufacturer
                                <input name="manufacturer" maxlength="160">
                            </label>
                            <label>
                                Lot number
                                <input name="lot_number" maxlength="120">
                            </label>
                            <label>
                                Clinic
                                <input name="source_name" maxlength="160">
                            </label>
                            <label>
                                Veterinarian
                                <input name="professional_name" maxlength="160">
                            </label>
                            <label>
                                Source
                                <select name="source_type">
                                    <option value="owner">Owner upload</option>
                                    <option value="clinic">Clinic</option>
                                    <option value="veterinarian">Veterinarian</option>
                                    <option value="import">Imported</option>
                                </select>
                            </label>
                            <label>
                                Dose
                                <input name="dose" maxlength="80">
                            </label>
                        </div>
                        <label>
                            Reaction
                            <textarea name="reaction" rows="2" maxlength="2000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-lucide-syringe class="icon icon--sm" aria-hidden="true" />
                                <span>Add vaccination</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section id="medications" class="medical-form-section scroll-mt-28" aria-labelledby="medication-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Prescription plan</p>
                            <h2 id="medication-form-title" class="mt-1 text-xl font-bold">Add medication course</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="medication">
                        <div class="medical-form-grid">
                            <label>
                                Medication name
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                Active ingredient
                                <input name="active_ingredient" maxlength="160">
                            </label>
                            <label>
                                Form
                                <select name="medication_form" required>
                                    <option value="tablet">Tablet</option>
                                    <option value="capsule">Capsule</option>
                                    <option value="liquid">Liquid</option>
                                    <option value="suspension">Suspension</option>
                                    <option value="drops">Drops</option>
                                    <option value="ointment">Ointment</option>
                                    <option value="injection">Injection by specialist</option>
                                    <option value="other">Other</option>
                                </select>
                            </label>
                            <label>
                                Concentration
                                <input name="concentration" maxlength="80">
                            </label>
                            <label>
                                Exact prescribed dose
                                <input name="dose" maxlength="120" required>
                            </label>
                            <label>
                                Route
                                <input name="route" maxlength="80" required placeholder="By mouth with food">
                            </label>
                            <label>
                                Schedule type
                                <select name="schedule_type" required>
                                    <option value="fixed">Fixed time</option>
                                    <option value="interval">Interval</option>
                                    <option value="as-needed">As needed</option>
                                    <option value="specific-days">Specific days</option>
                                </select>
                            </label>
                            <label>
                                Schedule
                                <input name="schedule_text" maxlength="180" required placeholder="Every 12 hours">
                            </label>
                            <label>
                                Starts
                                <input type="date" name="starts_on" value="{{ now()->toDateString() }}" required>
                            </label>
                            <label>
                                Ends
                                <input type="date" name="ends_on">
                            </label>
                            <label>
                                Next dose
                                <input type="datetime-local" name="next_dose_at">
                            </label>
                            <label>
                                Status
                                <select name="medication_status" required>
                                    @forelse ($entry_options['medication_statuses'] as $value => $label)
                                        <option value="{{ $value }}" @selected($value === 'active')>{{ $label }}</option>
                                    @empty
                                        <option disabled>No statuses</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                Prescribing specialist
                                <input name="professional_name" maxlength="160">
                            </label>
                            <label>
                                Clinic
                                <input name="source_name" maxlength="160">
                            </label>
                            <label>
                                Source
                                <select name="source_type">
                                    <option value="owner">Owner entry</option>
                                    <option value="veterinarian">Veterinarian</option>
                                    <option value="clinic">Clinic</option>
                                    <option value="import">Imported</option>
                                </select>
                            </label>
                            <label>
                                Reason
                                <input name="reason" maxlength="180">
                            </label>
                        </div>
                        <label>
                            Signed instructions
                            <textarea name="instructions" rows="4" maxlength="3000"></textarea>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="is_high_risk" value="1">
                            <span>Require extra attention for this medication</span>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-lucide-pill class="icon icon--sm" aria-hidden="true" />
                                <span>Add medication</span>
                            </button>
                        </div>
                    </form>
                </section>

                <section class="medical-form-section" aria-labelledby="reminder-form-title">
                    <div class="medical-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Care calendar</p>
                            <h2 id="reminder-form-title" class="mt-1 text-xl font-bold">Schedule reminder</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('medical-records.entries.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <input type="hidden" name="entry_type" value="reminder">
                        <div class="medical-form-grid">
                            <label>
                                Reminder type
                                <select name="reminder_type" required>
                                    <option value="vaccination">Vaccination</option>
                                    <option value="medication">Medication</option>
                                    <option value="appointment">Appointment</option>
                                    <option value="follow-up">Follow-up</option>
                                    <option value="lab-test">Lab test</option>
                                    <option value="weight">Weight</option>
                                    <option value="wound-care">Wound care</option>
                                    <option value="rehabilitation">Rehabilitation</option>
                                    <option value="document">Document renewal</option>
                                    <option value="prescription">Prescription renewal</option>
                                </select>
                            </label>
                            <label>
                                Title
                                <input name="title" maxlength="180" required>
                            </label>
                            <label>
                                Due
                                <input type="datetime-local" name="due_at" required>
                            </label>
                            <label>
                                Priority
                                <select name="priority" required>
                                    <option value="normal">Normal</option>
                                    <option value="important">Important</option>
                                    <option value="critical">Critical</option>
                                    <option value="low">Low</option>
                                </select>
                            </label>
                        </div>
                        <label>
                            Instructions
                            <textarea name="instructions" rows="2" maxlength="3000"></textarea>
                        </label>
                        <div class="flex justify-end">
                            <button class="action action--primary action--compact" type="submit">
                                <x-lucide-bell-plus class="icon icon--sm" aria-hidden="true" />
                                <span>Schedule</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="grid min-w-0 content-start gap-9">
                <section id="documents" class="medical-form-section scroll-mt-28" aria-labelledby="document-form-title">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">Private storage</p>
                        <h2 id="document-form-title" class="mt-1 text-xl font-bold">Upload document</h2>
                    </div>
                    <form method="POST" action="{{ route('medical-records.documents.store', $medical_record['slug']) }}" enctype="multipart/form-data" class="grid gap-4">
                        @csrf
                        <label>
                            Title
                            <input name="title" maxlength="180" required>
                        </label>
                        <label>
                            Type
                            <select name="document_type" required>
                                <option value="visit-summary">Visit summary</option>
                                <option value="lab-result">Lab result</option>
                                <option value="vaccination-certificate">Vaccination certificate</option>
                                <option value="prescription">Prescription</option>
                                <option value="imaging">Imaging</option>
                                <option value="surgery">Surgery</option>
                                <option value="insurance">Insurance</option>
                                <option value="travel">Travel</option>
                                <option value="invoice">Invoice</option>
                                <option value="other">Other</option>
                            </select>
                        </label>
                        <label>
                            Source
                            <select name="source_type" required>
                                <option value="owner">Owner upload</option>
                                <option value="clinic">Clinic</option>
                                <option value="veterinarian">Veterinarian</option>
                                <option value="laboratory">Laboratory</option>
                                <option value="import">Imported</option>
                            </select>
                        </label>
                        <label>
                            Source name
                            <input name="source_name" maxlength="160">
                        </label>
                        <label>
                            File
                            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp,.mp4,.mov,.mp3,.wav" required>
                            <span>PDF, image, short video, or audio up to 20 MB.</span>
                        </label>
                        <button class="action action--primary action--compact" type="submit">
                            <x-lucide-upload class="icon icon--sm" aria-hidden="true" />
                            <span>Upload privately</span>
                        </button>
                    </form>
                </section>

                <section id="access" class="medical-form-section scroll-mt-28" aria-labelledby="access-form-title">
                    <div>
                        <p class="text-xs font-bold uppercase text-paw-leaf">Temporary access</p>
                        <h2 id="access-form-title" class="mt-1 text-xl font-bold">Share selected sections</h2>
                    </div>
                    <form method="POST" action="{{ route('medical-records.access.store', $medical_record['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>
                            Recipient
                            <input name="recipient_name" maxlength="160" required placeholder="Clinic or caregiver name">
                        </label>
                        <label>
                            Role
                            <select name="recipient_role" required>
                                <option value="veterinarian">Veterinarian</option>
                                <option value="clinic">Clinic</option>
                                <option value="co-owner">Co-owner</option>
                                <option value="caregiver">Caregiver</option>
                                <option value="sitter">Sitter</option>
                                <option value="groomer">Groomer</option>
                                <option value="rehabilitation-specialist">Rehabilitation specialist</option>
                                <option value="shelter">Shelter</option>
                            </select>
                        </label>
                        <label>
                            Access label
                            <input name="label" maxlength="180" required value="Temporary care review">
                        </label>
                        <fieldset class="grid gap-2">
                            <legend class="text-sm font-bold">Visible sections</legend>
                            <div class="grid gap-2">
                                @forelse ($entry_options['share_sections'] as $value => $label)
                                    <label class="medical-check medical-check--boxed">
                                        <input type="checkbox" name="sections[]" value="{{ $value }}" @checked(in_array($value, ['summary', 'medications'], true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-paw-muted">No sections available.</p>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="grid grid-cols-2 gap-3">
                            <label>
                                Expires in
                                <select name="expires_in_hours">
                                    <option value="4">4 hours</option>
                                    <option value="24" selected>1 day</option>
                                    <option value="168">7 days</option>
                                    <option value="720">30 days</option>
                                </select>
                            </label>
                            <label>
                                View limit
                                <input type="number" name="max_views" value="5" min="1" max="100" required>
                            </label>
                        </div>
                        <label class="medical-check">
                            <input type="checkbox" name="allow_download" value="1">
                            <span>Allow selected document downloads</span>
                        </label>
                        <label class="medical-check">
                            <input type="checkbox" name="privacy_acknowledged" value="1" required>
                            <span>I reviewed the selected sections and expiry.</span>
                        </label>
                        <button class="action action--primary action--compact" type="submit">
                            <x-lucide-link class="icon icon--sm" aria-hidden="true" />
                            <span>Create access link</span>
                        </button>
                    </form>
                </section>

                <section class="medical-section" aria-labelledby="active-access-title">
                    <div class="medical-section__heading">
                        <h2 id="active-access-title" class="text-xl font-bold">Access grants</h2>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold">{{ $grant['recipient_name'] }}</h3>
                                        <p class="mt-1 text-xs text-paw-muted">{{ $grant['recipient_role'] }} · {{ $grant['status'] }}</p>
                                        <p class="mt-2 text-xs text-paw-muted">{{ implode(', ', $grant['sections']) }}</p>
                                        <p class="mt-1 text-xs font-semibold">Expires {{ $grant['expires_at'] }} · {{ $grant['views'] }} opens</p>
                                    </div>
                                    @if ($grant['active'])
                                        <form method="POST" action="{{ $grant['revoke_url'] }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="action action--surface action--compact" type="submit" title="Revoke access">
                                                <x-lucide-link-2-off class="icon icon--sm" aria-hidden="true" />
                                                <span class="sr-only">Revoke access for {{ $grant['recipient_name'] }}</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No access grants.</p>
                        @endforelse
                    </div>
                </section>

                <section class="medical-section" aria-labelledby="access-log-title">
                    <div class="medical-section__heading">
                        <h2 id="access-log-title" class="text-xl font-bold">Access history</h2>
                    </div>
                    <div class="medical-compact-list">
                        @forelse ($access_logs as $log)
                            <article>
                                <h3 class="font-bold">{{ $log['action'] }}</h3>
                                <p class="mt-1 text-xs text-paw-muted">{{ $log['actor'] }} · {{ $log['role'] }} · {{ $log['time'] }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No shared-link activity.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
