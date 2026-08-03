<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="care-journal-header">
            <div class="care-journal-header__identity">
                @if ($care_journal['image_url'])
                    <img src="{{ $care_journal['image_url'] }}" alt="{{ $care_journal['pet_name'] }}">
                @else
                    <span><x-ui-icon name="paw-print" size="2xl" /></span>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('care-journals.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-paw-leaf">
                            <x-ui-icon name="arrow-left" size="sm" />
                            <span>{{ __('ui.care_journals_efcbb402a3') }}</span>
                        </a>
                        <span class="text-paw-line">/</span>
                        <x-status-badge label="{{ __('ui.private_c63eb6720c') }}" icon="lock-keyhole" tone="surface" />
                    </div>
                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $care_journal['pet_name'] }}</h1>
                    <p class="mt-2 text-paw-muted">
                        {{ __('presentation.care_identity', ['species' => $care_journal['species'], 'breed' => $care_journal['breed'], 'caregiver' => $care_journal['current_caregiver']]) }}
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-action-control label="{{ __('ui.care_report_0d463d010a') }}" icon="printer" :href="$care_journal['report_url']" />
                <x-action-control label="{{ __('ui.plan_share_e65f71ff99') }}" icon="list-checks" variant="primary" :href="$care_journal['manage_url']" />
            </div>
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_care_action_was_not_saved_d0cfd1ce44') }}</strong>
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

        <x-care-today-summary :summary="$today_summary" />

        <div class="care-dashboard">
            <div class="grid min-w-0 content-start gap-5">
                <section class="care-section" aria-labelledby="quick-entry-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.quick_record_f3c0649efb') }}</p>
                            <h2 id="quick-entry-title" class="mt-1 text-xl font-bold">{{ __('ui.add_care_action_9831e5d6ff') }}</h2>
                        </div>
                        <x-status-badge label="{{ __('ui.private_entry_97b0e712f7') }}" icon="lock-keyhole" tone="surface" />
                    </div>
                    <x-care-entry-form
                        :action="route('care-journals.entries.store', $care_journal['slug'])"
                        :types="$entry_types"
                        :idempotency-key="$entry_idempotency_key"
                        :started-at="$form_defaults['started_at']"
                    />
                </section>

                <section class="care-section" aria-labelledby="timeline-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.recorded_facts_2c506eb576') }}</p>
                            <h2 id="timeline-title" class="mt-1 text-xl font-bold">{{ __('ui.recent_care_timeline_d1219c80ae') }}</h2>
                        </div>
                        <span class="text-sm font-semibold text-paw-muted">{{ trans_choice('presentation.entries_count', count($entries), ['count' => count($entries)]) }}</span>
                    </div>
                    <x-care-timeline :entries="$entries" />
                </section>

                <section class="care-section" aria-labelledby="weekly-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.seven_day_view_26ef2d0200') }}</p>
                            <h2 id="weekly-title" class="mt-1 text-xl font-bold">{{ __('ui.care_patterns_313001343f') }}</h2>
                        </div>
                    </div>
                    <p class="care-section-note">{{ __('ui.bars_show_recorded_walk_and_activity_minutes_no_deb41add80') }}</p>
                    <x-care-weekly-table :days="$weekly" />
                </section>
            </div>

            <aside class="grid min-w-0 content-start gap-5">
                <section class="care-section" aria-labelledby="tasks-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.household_handoff_ee017f0017') }}</p>
                            <h2 id="tasks-title" class="mt-1 text-xl font-bold">{{ __('ui.open_tasks_87cfa1a507') }}</h2>
                        </div>
                        <a href="{{ $care_journal['manage_url'] }}#tasks" class="inline-flex items-center gap-1 text-sm font-bold text-paw-leaf">
                            <x-ui-icon name="settings-2" size="sm" />
                            <span>{{ __('ui.manage_5a23444828') }}</span>
                        </a>
                    </div>
                    <x-care-task-list :tasks="$tasks" :journal-slug="$care_journal['slug']" />
                </section>

                <section class="care-section" aria-labelledby="medication-source-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.single_source_of_truth_89951f4ad4') }}</p>
                            <h2 id="medication-source-title" class="mt-1 text-xl font-bold">{{ __('ui.medication_today_1e22b63cec') }}</h2>
                        </div>
                        <a href="{{ $medical['record_url'] }}" class="inline-flex items-center gap-1 text-sm font-bold text-paw-leaf">
                            <x-ui-icon name="heart-pulse" size="sm" />
                            <span>{{ __('ui.health_record_6ab2c69d2f') }}</span>
                        </a>
                    </div>
                    <div class="care-source-notice">
                        <x-ui-icon name="pill" size="lg" />
                        <p>{{ __('ui.doses_are_recorded_in_the_medical_record_and_5a75aee169') }}</p>
                    </div>
                    <div class="care-compact-list">
                        @forelse ($medical['active'] as $medication)
                            <article>
                                <div>
                                    <h3 class="font-bold">{{ $medication['name'] }}</h3>
                                    <p class="mt-1 text-sm text-paw-muted">{{ $medication['dose'] }} · {{ $medication['schedule'] }}</p>
                                    <p class="mt-1 text-xs font-semibold text-paw-muted">
                                        {{ __('presentation.next_medication', ['date' => $medication['next_dose'] ?: __('ui.as_instructed_d5df7cf601')]) }}
                                        @if ($medication['latest_status'])
                                            · {{ __('presentation.latest_medication', ['status' => $medication['latest_status'], 'name' => $medication['latest_by']]) }}
                                        @endif
                                    </p>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ $medical['record_exists'] ? __('ui.no_active_medication_courses_b22fc4082d') : __('ui.create_or_open_a_health_record_to_manage_1cd3664099') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="care-section" aria-labelledby="routines-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.current_plan_37ca715f53') }}</p>
                            <h2 id="routines-title" class="mt-1 text-xl font-bold">{{ __('ui.active_routines_8720ad09ac') }}</h2>
                        </div>
                    </div>
                    <div class="care-compact-list">
                        @forelse ($routines as $routine)
                            <article>
                                <span class="care-list-icon"><x-ui-icon name="repeat-2" size="sm" /></span>
                                <div>
                                    <h3 class="font-bold">{{ $routine['name'] }}</h3>
                                    <p class="mt-1 text-sm text-paw-muted">{{ $routine['period'] }} · {{ $routine['start_time'] ?: __('ui.flexible_time_0d0eb23930') }}</p>
                                    @if ($routine['instructions'])
                                        <p class="mt-1 text-xs leading-5 text-paw-muted">{{ $routine['instructions'] }}</p>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_routine_templates_yet_e789f0fd19') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
