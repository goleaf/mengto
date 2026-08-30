<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-6xl gap-7">
        <header class="care-shared-header">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge label="{{ __('ui.temporary_private_access') }}" icon="key-round" tone="success" size="regular" />
                    <x-status-badge :label="$grant['recipient_role']" icon="user-round-check" tone="surface" />
                </div>
                <h1 class="mt-4 text-3xl font-bold sm:text-4xl">{{ __('presentation.pet_care_plan', ['pet' => $care_journal['pet_name']]) }}</h1>
                <p class="mt-2 text-paw-muted">{{ __('presentation.access_expires', ['label' => $grant['label'], 'expires' => $grant['expires_at']]) }}</p>
            </div>
            <div class="care-shared-header__pet">
                @if ($care_journal['image_url'])
                    <img src="{{ $care_journal['image_url'] }}" alt="{{ $care_journal['pet_name'] }}" width="1200" height="900">
                @else
                    <x-ui-icon name="paw-print" size="xl" />
                @endif
            </div>
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_report_was_not_saved') }}</strong>
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

        <section class="care-access-scope" aria-label="{{ __('ui.temporary_access_limits') }}">
            <x-ui-icon name="shield-check" size="lg" />
            <div>
                <strong>{{ __('presentation.access_limited_to', ['sections' => implode(', ', $grant['sections'])]) }}</strong>
                <p>
                    {{ $grant['allow_add'] ? __('ui.you_may_add_structured_reports') : __('ui.read_only_access') }}
                    {{ $grant['allow_location'] ? __('ui.location_sharing_is_enabled') : __('ui.exact_locations_remain_hidden') }}
                    {{ $grant['allow_media'] ? __('ui.media_is_enabled') : __('ui.private_media_remains_hidden') }}
                </p>
            </div>
        </section>

        <div class="care-dashboard">
            <div class="grid min-w-0 content-start gap-5">
                @if ($grant['allow_add'] && $entry_types)
                    <section class="care-section" aria-labelledby="shared-entry-title">
                        <div class="care-section__heading">
                            <div>
                                <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.sitter_or_specialist_report') }}</p>
                                <h2 id="shared-entry-title" class="mt-1 text-xl font-bold">{{ __('ui.record_completed_care') }}</h2>
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
                            submit-label="{{ __('ui.submit_care_report') }}"
                            compact
                        />
                    </section>
                @endif

                <section class="care-section" aria-labelledby="shared-timeline-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.allowed_history') }}</p>
                            <h2 id="shared-timeline-title" class="mt-1 text-xl font-bold">{{ __('ui.care_timeline') }}</h2>
                        </div>
                    </div>
                    <x-care-timeline :entries="$entries" />
                </section>
            </div>
            <aside class="grid min-w-0 content-start gap-5">
                <section class="care-section" aria-labelledby="shared-task-title">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.assigned_plan') }}</p>
                            <h2 id="shared-task-title" class="mt-1 text-xl font-bold">{{ __('ui.open_tasks') }}</h2>
                        </div>
                    </div>
                    <x-care-task-list :tasks="$tasks" read-only />
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
