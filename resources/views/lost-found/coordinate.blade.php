<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <x-detail-navigation
                    :href="route('lost-found.show', $search_case['slug'])"
                    :label="__('ui.public_report')"
                />
                <p class="mt-5 text-sm font-bold uppercase text-paw-coral">{{ __('ui.private_coordination_workspace') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('presentation.pet_search', ['pet' => $search_case['pet_name']]) }}</h1>
                <p class="mt-2 text-paw-muted">{{ $search_case['public_code'] }} · {{ $search_case['status_label'] }} · {{ $search_case['last_seen_area'] }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-action-control label="{{ __('ui.public_report') }}" icon="external-link" variant="surface" :href="route('lost-found.show', $search_case['slug'])" />
                <x-action-control label="{{ __('ui.print_poster') }}" icon="printer" variant="surface" :href="route('lost-found.poster', $search_case['slug'])" />
            </div>
        </header>

        @if ($errors->any())
            <div class="rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-900" role="alert">
                <div class="flex items-center gap-2 font-bold">
                    <x-ui-icon name="circle-alert" size="lg" />
                    {{ __('ui.action_could_not_be_completed') }}
                </div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>{{ __('ui.review_the_submitted_values') }}</li>
                    @endforelse
                </ul>
            </div>
        @endif

        <section class="grid gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line md:grid-cols-4" aria-label="{{ __('ui.coordination_status') }}">
            @forelse ([
                ['label' => __('ui.sightings'), 'value' => count($sightings), 'icon' => 'eye'],
                ['label' => __('ui.sectors'), 'value' => count($sectors), 'icon' => 'map'],
                ['label' => __('ui.tasks'), 'value' => count($tasks), 'icon' => 'list-checks'],
                ['label' => __('ui.volunteers'), 'value' => count($volunteers), 'icon' => 'users-round'],
            ] as $stat)
                <div class="flex items-center gap-3 bg-white p-4">
                    <x-ui-icon size="lg" :name="$stat['icon']" class="text-paw-leaf" />
                    <div><strong class="block text-xl">{{ $stat['value'] }}</strong><span class="text-xs text-paw-muted">{{ $stat['label'] }}</span></div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">{{ __('ui.no_coordination_totals') }}</p>
            @endforelse
        </section>

        <div class="grid gap-7 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,.65fr)]">
            <div class="grid content-start gap-8">
                <x-search-map :markers="$map_markers" :title="__('ui.team_map_for').' '.$search_case['pet_name']" />

                <section aria-labelledby="sightings-review-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="sightings-review-title" class="text-2xl font-bold">{{ __('ui.sighting_review') }}</h2>
                        <span class="text-sm text-paw-muted">{{ __('presentation.submitted_count', ['count' => count($sightings)]) }}</span>
                    </div>
                    <div class="mt-4 grid gap-3">
                        @forelse ($sightings as $sighting)
                            <article class="grid gap-4 rounded-md border border-paw-line bg-white p-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold">{{ $sighting['public_area'] }}</h3>
                                        <span class="rounded bg-paw-sun/60 px-2 py-1 text-xs font-bold">{{ $sighting['status_label'] }}</span>
                                        @if ($sighting['risk_flags'])
                                            <span class="rounded bg-red-100 px-2 py-1 text-xs font-bold text-red-800">{{ __('ui.review_risk') }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-paw-muted">{{ __('presentation.observed_and_sent', ['reporter' => $sighting['reporter_name'], 'observed' => $sighting['observed_label'], 'sent' => $sighting['submitted_label']]) }}</p>
                                    @if ($sighting['notes'])
                                        <p class="mt-3 text-sm leading-6">{{ $sighting['notes'] }}</p>
                                    @endif
                                    <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-3">
                                        <div><dt class="text-paw-muted">{{ __('ui.confidence') }}</dt><dd class="font-semibold">{{ $sighting['confidence'] }}</dd></div>
                                        <div><dt class="text-paw-muted">{{ __('ui.contact') }}</dt><dd class="font-semibold">{{ $sighting['contact_status'] }}</dd></div>
                                        <div><dt class="text-paw-muted">{{ __('ui.direction') }}</dt><dd class="font-semibold">{{ $sighting['direction'] ?: __('ui.unknown') }}</dd></div>
                                    </dl>
                                    @if ($sighting['exact_location'])
                                        <p class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-paw-coral">
                                            <x-ui-icon name="lock-keyhole" size="sm" />
                                            {{ __('presentation.exact_point', ['latitude' => $sighting['exact_location']['latitude'], 'longitude' => $sighting['exact_location']['longitude']]) }}
                                        </p>
                                    @endif
                                </div>
                                @if (! in_array($sighting['status'], ['confirmed', 'rejected'], true))
                                    <div class="flex gap-2 lg:flex-col">
                                        <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="confirm-sighting">
                                            <input type="hidden" name="sighting_id" value="{{ $sighting['id'] }}">
                                            <button type="submit" class="action action--primary action--compact w-full">
                                                <x-ui-icon name="map-pin-check" size="sm" />
                                                <span>{{ __('ui.confirm') }}</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="reject-sighting">
                                            <input type="hidden" name="sighting_id" value="{{ $sighting['id'] }}">
                                            <button type="submit" class="action action--surface action--compact w-full">
                                                <x-ui-icon name="x" size="sm" />
                                                <span>{{ __('ui.reject') }}</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted">{{ __('ui.no_sightings_have_been_submitted') }}</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="sectors-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="sectors-title" class="text-2xl font-bold">{{ __('ui.search_sectors') }}</h2>
                        <span class="text-sm text-paw-muted">{{ trans_choice('presentation.sectors_count', count($sectors), ['count' => count($sectors)]) }}</span>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($sectors as $sector)
                            <article class="rounded-md border border-paw-line bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div><p class="text-xs font-bold uppercase text-paw-muted">{{ $sector['code'] }}</p><h3 class="mt-1 font-bold">{{ $sector['label'] }}</h3></div>
                                    <span class="rounded bg-paw-mint px-2 py-1 text-xs font-bold text-paw-leaf">{{ __('presentation.priority', ['priority' => $sector['priority']]) }}</span>
                                </div>
                                <p class="mt-3 text-sm font-semibold">{{ $sector['status_label'] }}</p>
                                @if ($sector['risk_notes'])<p class="mt-2 text-xs leading-5 text-paw-coral">{{ $sector['risk_notes'] }}</p>@endif
                                @if ($sector['access_notes'])<p class="mt-2 text-xs leading-5 text-paw-muted">{{ $sector['access_notes'] }}</p>@endif
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted sm:col-span-2 lg:col-span-3">{{ __('ui.create_sectors_before_assigning_area_tasks') }}</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="tasks-work-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="tasks-work-title" class="text-2xl font-bold">{{ __('ui.volunteer_tasks') }}</h2>
                        <span class="text-sm text-paw-muted">{{ __('presentation.total_count', ['count' => count($tasks)]) }}</span>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @forelse ($tasks as $task)
                            <article class="rounded-md border border-paw-line bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="font-bold">{{ $task['title'] }}</h3>
                                    <span class="rounded bg-paw-sun/60 px-2 py-1 text-xs font-bold">{{ $task['status_label'] }}</span>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-paw-muted">{{ $task['description'] }}</p>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                                    @if ($task['sector'])<span>{{ $task['sector'] }}</span>@endif
                                    <span>{{ $task['safety_label'] }}</span>
                                    @if ($task['assignee_name'])<span>{{ $task['assignee_name'] }}</span>@endif
                                </div>
                                @if ($task['is_actor_assignee'] && $task['status'] === 'claimed')
                                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="action" value="start-task">
                                        <input type="hidden" name="task_id" value="{{ $task['id'] }}">
                                        <button type="submit" class="action action--surface action--compact w-full">
                                            <x-ui-icon name="play" size="sm" />
                                            <span>{{ __('ui.start_task') }}</span>
                                        </button>
                                    </form>
                                @elseif ($task['is_actor_assignee'] && $task['status'] === 'in-progress')
                                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="mt-4 grid gap-2">
                                        @csrf
                                        <input type="hidden" name="action" value="complete-task">
                                        <input type="hidden" name="task_id" value="{{ $task['id'] }}">
                                        <label class="grid gap-1 text-xs font-semibold">
                                            {{ __('ui.result') }}
                                            <textarea name="task_result" rows="2" class="rounded-md border border-paw-line px-3 py-2" required maxlength="2000"></textarea>
                                        </label>
                                        <button type="submit" class="action action--primary action--compact w-full">
                                            <x-ui-icon name="check" size="sm" />
                                            <span>{{ __('ui.complete') }}</span>
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted sm:col-span-2">{{ __('ui.no_volunteer_tasks_yet') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h2 class="text-xl font-bold">{{ __('ui.volunteers') }}</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($volunteers as $volunteer)
                                <article class="rounded-md border border-paw-line bg-white p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="font-bold">{{ $volunteer['display_name'] }}</h3>
                                        <span class="text-xs font-semibold text-paw-leaf">{{ $volunteer['status_label'] }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-paw-muted">{{ implode(' · ', $volunteer['capabilities']) ?: __('ui.available_for_general_coordination') }}</p>
                                </article>
                            @empty
                                <p class="text-sm text-paw-muted">{{ __('ui.no_volunteers_have_joined') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-bold">{{ __('ui.alert_delivery') }}</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($alerts as $alert)
                                <article class="rounded-md border border-paw-line bg-white p-4">
                                    <div class="flex items-center justify-between gap-3"><h3 class="font-bold">{{ $alert['kind'] }}</h3><span class="text-xs font-semibold">{{ ucfirst($alert['status']) }}</span></div>
                                    <p class="mt-2 text-sm text-paw-muted">{{ $alert['region'] }} · {{ __('presentation.kilometers', ['count' => $alert['radius_km']]) }}</p>
                                    <p class="mt-2 text-xs font-semibold">{{ trans_choice('presentation.recipients_count', $alert['recipient_count'], ['count' => $alert['recipient_count']]) }}</p>
                                </article>
                            @empty
                                <p class="text-sm text-paw-muted">{{ __('ui.no_alert_records') }}</p>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h2 class="text-xl font-bold">{{ __('lost_found.interface.contact_relays') }}</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($contact_relays as $relay)
                                <article class="rounded-md border border-paw-line bg-white p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h3 class="font-bold">{{ $relay['sender_name'] }}</h3>
                                        <time class="text-xs text-paw-muted">{{ $relay['created_label'] }}</time>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-paw-leaf">{{ $relay['purpose'] }}</p>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6">{{ $relay['message'] }}</p>
                                </article>
                            @empty
                                <p class="rounded-md border border-dashed border-paw-line p-5 text-sm text-paw-muted">{{ __('lost_found.interface.no_contact_relays') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-bold">{{ __('lost_found.interface.case_events') }}</h2>
                        <ol class="mt-4 grid gap-3">
                            @forelse ($events as $event)
                                <li class="rounded-md border border-paw-line bg-white p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="font-bold">{{ $event['label'] }}</p>
                                        <time class="text-xs text-paw-muted">{{ $event['created_label'] }}</time>
                                    </div>
                                    @if ($event['previous_status'] && $event['current_status'])
                                        <p class="mt-2 text-sm text-paw-muted">{{ __('lost_found.interface.status_transition', ['from' => $event['previous_status'], 'to' => $event['current_status']]) }}</p>
                                    @endif
                                    @if ($event['actor_name'])
                                        <p class="mt-1 text-xs font-semibold">{{ $event['actor_name'] }}</p>
                                    @endif
                                </li>
                            @empty
                                <li class="rounded-md border border-dashed border-paw-line p-5 text-sm text-paw-muted">{{ __('lost_found.interface.no_case_events') }}</li>
                            @endforelse
                        </ol>
                    </div>
                </section>
            </div>

            <aside class="grid content-start gap-5 xl:sticky xl:top-24 xl:self-start">
                <section class="rounded-md border border-paw-line bg-white p-4">
                    <div class="flex items-center gap-2">
                        <x-ui-icon name="lock-keyhole" size="lg" class="text-paw-leaf" />
                        <h2 class="font-bold">{{ __('ui.private_case_details') }}</h2>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div><dt class="text-paw-muted">{{ __('ui.exact_point') }}</dt><dd class="mt-1 font-semibold">{{ $search_case['exact_location']['latitude'] ?? __('ui.not_set') }}, {{ $search_case['exact_location']['longitude'] ?? __('ui.not_set') }}</dd></div>
                        @if ($search_case['exact_location']['note'] ?? null)<div><dt class="text-paw-muted">{{ __('ui.location_note') }}</dt><dd class="mt-1 font-semibold">{{ $search_case['exact_location']['note'] }}</dd></div>@endif
                        @if ($search_case['hidden_marks'])<div><dt class="text-paw-muted">{{ __('ui.hidden_mark') }}</dt><dd class="mt-1 font-semibold">{{ $search_case['hidden_marks'] }}</dd></div>@endif
                        <div><dt class="text-paw-muted">{{ __('ui.protected_contact') }}</dt><dd class="mt-1 font-semibold">{{ $search_case['contact_details']['channel'] ?? 'platform' }} · {{ $search_case['contact_details']['value'] ?? 'owner account' }}</dd></div>
                    </dl>
                </section>

                <details open class="rounded-md border border-paw-line bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-2 p-4 font-bold">
                        <x-ui-icon name="radio-tower" size="lg" class="text-paw-coral" />
                        {{ __('ui.publish_update') }}
                    </summary>
                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <input type="hidden" name="action" value="publish-update">
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.title') }}<input name="update_title" class="rounded-md border border-paw-line px-3 py-2" required maxlength="160"></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.update') }}<textarea name="update_body" rows="3" class="rounded-md border border-paw-line px-3 py-2" maxlength="2000"></textarea></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.public_area') }}<input name="update_area" class="rounded-md border border-paw-line px-3 py-2" maxlength="160"></label>
                        <button type="submit" class="action action--primary w-full"><x-ui-icon name="send" /><span>{{ __('ui.publish') }}</span></button>
                    </form>
                </details>

                <details class="rounded-md border border-paw-line bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-2 p-4 font-bold">
                        <x-ui-icon name="grid-2x2-plus" size="lg" class="text-paw-leaf" />
                        {{ __('ui.add_sector') }}
                    </summary>
                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <input type="hidden" name="action" value="create-sector">
                        <div class="grid grid-cols-[6rem_1fr] gap-2">
                            <label class="grid gap-1 text-sm font-semibold">{{ __('ui.code') }}<input name="sector_code" class="min-w-0 rounded-md border border-paw-line px-3 py-2" required maxlength="30" placeholder="{{ __('ui.a1') }}"></label>
                            <label class="grid gap-1 text-sm font-semibold">{{ __('ui.label') }}<input name="sector_label" class="min-w-0 rounded-md border border-paw-line px-3 py-2" required maxlength="120"></label>
                        </div>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.priority') }}<select name="sector_priority" class="rounded-md border border-paw-line px-3 py-2"><option value="1">{{ __('ui.high') }}</option><option value="2" selected>{{ __('ui.normal') }}</option><option value="3">{{ __('ui.low') }}</option></select></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.safety_notes') }}<textarea name="sector_risk_notes" rows="2" class="rounded-md border border-paw-line px-3 py-2" maxlength="1000"></textarea></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.access_notes') }}<textarea name="sector_access_notes" rows="2" class="rounded-md border border-paw-line px-3 py-2" maxlength="1000"></textarea></label>
                        <button type="submit" class="action action--surface w-full"><x-ui-icon name="plus" /><span>{{ __('ui.create_sector') }}</span></button>
                    </form>
                </details>

                <details class="rounded-md border border-paw-line bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-2 p-4 font-bold">
                        <x-ui-icon name="list-plus" size="lg" class="text-paw-leaf" />
                        {{ __('ui.add_task') }}
                    </summary>
                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <input type="hidden" name="action" value="create-task">
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.task_type') }}<select name="task_type" class="rounded-md border border-paw-line px-3 py-2">@forelse ($task_types as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@empty<option disabled>{{ __('ui.no_types') }}</option>@endforelse</select></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.sector') }}<select name="sector_id" class="rounded-md border border-paw-line px-3 py-2"><option value="">{{ __('ui.no_sector') }}</option>@forelse ($sectors as $sector)<option value="{{ $sector['id'] }}">{{ $sector['code'] }} · {{ $sector['label'] }}</option>@empty<option disabled>{{ __('ui.no_sectors_yet') }}</option>@endforelse</select></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.title') }}<input name="task_title" class="rounded-md border border-paw-line px-3 py-2" required maxlength="140"></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.instructions') }}<textarea name="task_description" rows="3" class="rounded-md border border-paw-line px-3 py-2" required maxlength="2000"></textarea></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.safety') }}<select name="safety_level" class="rounded-md border border-paw-line px-3 py-2"><option value="standard">{{ __('ui.standard') }}</option><option value="pair-required">{{ __('ui.pair_required') }}</option><option value="specialist-only">{{ __('ui.specialist_only') }}</option><option value="dangerous">{{ __('ui.dangerous_area') }}</option></select></label>
                        <button type="submit" class="action action--surface w-full"><x-ui-icon name="plus" /><span>{{ __('ui.create_task') }}</span></button>
                    </form>
                </details>

                <details class="rounded-md border border-paw-coral/40 bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-2 p-4 font-bold">
                        <x-ui-icon name="circle-dot-dashed" size="lg" class="text-paw-coral" />
                        {{ __('ui.change_search_status') }}
                    </summary>
                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <input type="hidden" name="action" value="update-status">
                        <input type="hidden" name="lock_version" value="{{ $search_case['lock_version'] }}">
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.status') }}<select name="status" class="rounded-md border border-paw-line px-3 py-2">@forelse ($statuses as $value => $label)<option value="{{ $value }}" @selected($search_case['status'] === $value)>{{ $label }}</option>@empty<option disabled>{{ __('ui.no_statuses') }}</option>@endforelse</select></label>
                        <label class="grid gap-1 text-sm font-semibold">{{ __('ui.public_note') }}<textarea name="status_note" rows="3" class="rounded-md border border-paw-line px-3 py-2" maxlength="1500"></textarea></label>
                        <label class="flex min-h-11 items-center gap-3 text-xs"><input type="checkbox" name="return_confirmed" value="1"><span>{{ __('lost_found.interface.return_confirmation') }}</span></label>
                        <button type="submit" class="action action--primary w-full"><x-ui-icon name="refresh-cw" /><span>{{ __('ui.update_status') }}</span></button>
                    </form>
                </details>

                @if ($search_case['can_archive'])
                    <details class="rounded-md border border-paw-line bg-white">
                        <summary class="flex min-h-11 cursor-pointer list-none items-center gap-2 p-4 font-bold">
                            <x-ui-icon name="archive" size="lg" class="text-paw-muted" />
                            {{ __('lost_found.interface.archive_case') }}
                        </summary>
                        <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                            @csrf
                            <input type="hidden" name="action" value="archive-case">
                            <input type="hidden" name="lock_version" value="{{ $search_case['lock_version'] }}">
                            <p class="text-sm leading-6 text-paw-muted">{{ __('lost_found.interface.archive_case_help') }}</p>
                            <label class="flex min-h-11 items-start gap-3 text-sm">
                                <input type="checkbox" name="archive_confirmed" value="1" class="mt-1" required>
                                <span>{{ __('lost_found.interface.archive_confirmation') }}</span>
                            </label>
                            <button type="submit" class="action action--surface min-h-11 w-full">
                                <x-ui-icon name="archive" />
                                <span>{{ __('lost_found.interface.archive_case') }}</span>
                            </button>
                        </form>
                    </details>
                @endif
            </aside>
        </div>
    </div>
</x-app-shell>
