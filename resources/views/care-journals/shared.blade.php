<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-6xl gap-7">
        <header class="care-shared-header">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge label="{{ __('ui.temporary_private_access_9960aba82b') }}" icon="key-round" tone="success" size="regular" />
                    <x-status-badge :label="$grant['recipient_role']" icon="user-round-check" tone="surface" />
                </div>
                <h1 class="mt-4 text-3xl font-bold sm:text-4xl">{{ __('presentation.pet_care_plan', ['pet' => $care_journal['pet_name']]) }}</h1>
                <p class="mt-2 text-paw-muted">{{ __('presentation.access_expires', ['label' => $grant['label'], 'expires' => $grant['expires_at']]) }}</p>
            </div>
            <div class="care-shared-header__pet">
                @if ($care_journal['image_url'])
                    <img src="{{ $care_journal['image_url'] }}" alt="{{ $care_journal['pet_name'] }}">
                @else
                    <x-lucide-paw-print class="size-6" aria-hidden="true" />
                @endif
            </div>
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>{{ __('ui.the_report_was_not_saved_5503500502') }}</strong>
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

        <section class="care-access-scope" aria-label="{{ __('ui.temporary_access_limits_2259af7d73') }}">
            <x-lucide-shield-check class="size-5" aria-hidden="true" />
            <div>
                <strong>{{ __('presentation.access_limited_to', ['sections' => implode(', ', $grant['sections'])]) }}</strong>
                <p>
                    {{ $grant['allow_add'] ? __('ui.you_may_add_structured_reports_16a0ff5e60') : __('ui.read_only_access_14284bb92c') }}
                    {{ $grant['allow_location'] ? __('ui.location_sharing_is_enabled_fa7f5eb0ef') : __('ui.exact_locations_remain_hidden_cbb913bf99') }}
                    {{ $grant['allow_media'] ? __('ui.media_is_enabled_52ec773bfd') : __('ui.private_media_remains_hidden_fc8f84f6cb') }}
                </p>
            </div>
        </section>

        <div class="care-dashboard">
            <div class="grid min-w-0 content-start gap-5">
                @if ($grant['allow_add'] && $entry_types)
                    <section class="care-section" aria-labelledby="shared-entry-title">
                        <div class="care-section__heading">
                            <div>
                                <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.sitter_or_specialist_report_29848f5a2c') }}</p>
                                <h2 id="shared-entry-title" class="mt-1 text-xl font-bold">{{ __('ui.record_completed_care_90a24f2922') }}</h2>
                            </div>
                        </div>
                        <x-care-entry-form
                            :action="route('care-access.entries.store', ['token' => $token])"
                            :types="$entry_types"
                            :idempotency-key="$entry_idempotency_key"
                            :started-at="$form_defaults['started_at']"
                            :source-type="$grant['recipient_role'] === 'Sitter' ? 'sitter' : 'specialist'"
                            :source-name="$grant['recipient_name']"
                            :allow-location="$grant['allow_location']"
                            :allow-media="$grant['allow_media']"
                            submit-label="{{ __('ui.submit_care_report_d960917c43') }}"
                            compact
                        />
                    </section>
                @endif

                <section class="care-section" aria-labelledby="shared-timeline-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.allowed_history_0b92c02cc8') }}</p>
                            <h2 id="shared-timeline-title" class="mt-1 text-xl font-bold">{{ __('ui.care_timeline_73af4b6545') }}</h2>
                        </div>
                    </div>
                    <x-care-timeline :entries="$entries" />
                </section>
            </div>
            <aside class="grid min-w-0 content-start gap-5">
                <section class="care-section" aria-labelledby="shared-task-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.assigned_plan_6be7ae6b34') }}</p>
                            <h2 id="shared-task-title" class="mt-1 text-xl font-bold">{{ __('ui.open_tasks_87cfa1a507') }}</h2>
                        </div>
                    </div>
                    <x-care-task-list :tasks="$tasks" read-only />
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
