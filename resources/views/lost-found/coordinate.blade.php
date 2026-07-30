<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <a href="{{ route('lost-found.show', $search_case['slug']) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-paw-leaf">
                    <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                    Public report
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-coral">Private coordination workspace</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $search_case['pet_name'] }} search</h1>
                <p class="mt-2 text-paw-muted">{{ $search_case['public_code'] }} · {{ $search_case['status_label'] }} · {{ $search_case['last_seen_area'] }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-action-control label="Public report" icon="external-link" variant="surface" :href="route('lost-found.show', $search_case['slug'])" />
                <x-action-control label="Print poster" icon="printer" variant="surface" :href="route('lost-found.poster', $search_case['slug'])" />
            </div>
        </header>

        @if ($errors->any())
            <div class="rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-900" role="alert">
                <div class="flex items-center gap-2 font-bold">
                    <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                    Action could not be completed
                </div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>Review the submitted values.</li>
                    @endforelse
                </ul>
            </div>
        @endif

        <section class="grid gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line md:grid-cols-4" aria-label="Coordination status">
            @forelse ([
                ['label' => 'Sightings', 'value' => count($sightings), 'icon' => 'eye'],
                ['label' => 'Sectors', 'value' => count($sectors), 'icon' => 'map'],
                ['label' => 'Tasks', 'value' => count($tasks), 'icon' => 'list-checks'],
                ['label' => 'Volunteers', 'value' => count($volunteers), 'icon' => 'users-round'],
            ] as $stat)
                <div class="flex items-center gap-3 bg-white p-4">
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" class="size-5 text-paw-leaf" aria-hidden="true" />
                    <div><strong class="block text-xl">{{ $stat['value'] }}</strong><span class="text-xs text-paw-muted">{{ $stat['label'] }}</span></div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">No coordination totals.</p>
            @endforelse
        </section>

        <div class="grid gap-7 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,.65fr)]">
            <div class="grid content-start gap-8">
                <x-search-map :markers="$map_markers" :title="'Team map for '.$search_case['pet_name']" />

                <section aria-labelledby="sightings-review-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="sightings-review-title" class="text-2xl font-bold">Sighting review</h2>
                        <span class="text-sm text-paw-muted">{{ count($sightings) }} submitted</span>
                    </div>
                    <div class="mt-4 grid gap-3">
                        @forelse ($sightings as $sighting)
                            <article class="grid gap-4 rounded-md border border-paw-line bg-white p-4 lg:grid-cols-[minmax(0,1fr)_auto]">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold">{{ $sighting['public_area'] }}</h3>
                                        <span class="rounded bg-paw-sun/60 px-2 py-1 text-xs font-bold">{{ $sighting['status_label'] }}</span>
                                        @if ($sighting['risk_flags'])
                                            <span class="rounded bg-red-100 px-2 py-1 text-xs font-bold text-red-800">Review risk</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-paw-muted">{{ $sighting['reporter_name'] }} · observed {{ $sighting['observed_label'] }} · sent {{ $sighting['submitted_label'] }}</p>
                                    @if ($sighting['notes'])
                                        <p class="mt-3 text-sm leading-6">{{ $sighting['notes'] }}</p>
                                    @endif
                                    <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-3">
                                        <div><dt class="text-paw-muted">Confidence</dt><dd class="font-semibold">{{ $sighting['confidence'] }}</dd></div>
                                        <div><dt class="text-paw-muted">Contact</dt><dd class="font-semibold">{{ $sighting['contact_status'] }}</dd></div>
                                        <div><dt class="text-paw-muted">Direction</dt><dd class="font-semibold">{{ $sighting['direction'] ?: 'Unknown' }}</dd></div>
                                    </dl>
                                    @if ($sighting['exact_location'])
                                        <p class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-paw-coral">
                                            <x-lucide-lock-keyhole class="size-3.5" aria-hidden="true" />
                                            Exact point {{ $sighting['exact_location']['latitude'] }}, {{ $sighting['exact_location']['longitude'] }}
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
                                                <x-lucide-map-pin-check class="icon icon--sm" aria-hidden="true" />
                                                <span>Confirm</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="reject-sighting">
                                            <input type="hidden" name="sighting_id" value="{{ $sighting['id'] }}">
                                            <button type="submit" class="action action--surface action--compact w-full">
                                                <x-lucide-x class="icon icon--sm" aria-hidden="true" />
                                                <span>Reject</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted">No sightings have been submitted.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="sectors-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="sectors-title" class="text-2xl font-bold">Search sectors</h2>
                        <span class="text-sm text-paw-muted">{{ count($sectors) }} sectors</span>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($sectors as $sector)
                            <article class="rounded-md border border-paw-line bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div><p class="text-xs font-bold uppercase text-paw-muted">{{ $sector['code'] }}</p><h3 class="mt-1 font-bold">{{ $sector['label'] }}</h3></div>
                                    <span class="rounded bg-paw-mint px-2 py-1 text-xs font-bold text-paw-leaf">P{{ $sector['priority'] }}</span>
                                </div>
                                <p class="mt-3 text-sm font-semibold">{{ $sector['status_label'] }}</p>
                                @if ($sector['risk_notes'])<p class="mt-2 text-xs leading-5 text-paw-coral">{{ $sector['risk_notes'] }}</p>@endif
                                @if ($sector['access_notes'])<p class="mt-2 text-xs leading-5 text-paw-muted">{{ $sector['access_notes'] }}</p>@endif
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted sm:col-span-2 lg:col-span-3">Create sectors before assigning area tasks.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="tasks-work-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="tasks-work-title" class="text-2xl font-bold">Volunteer tasks</h2>
                        <span class="text-sm text-paw-muted">{{ count($tasks) }} total</span>
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
                                            <x-lucide-play class="icon icon--sm" aria-hidden="true" />
                                            <span>Start task</span>
                                        </button>
                                    </form>
                                @elseif ($task['is_actor_assignee'] && $task['status'] === 'in-progress')
                                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="mt-4 grid gap-2">
                                        @csrf
                                        <input type="hidden" name="action" value="complete-task">
                                        <input type="hidden" name="task_id" value="{{ $task['id'] }}">
                                        <label class="grid gap-1 text-xs font-semibold">
                                            Result
                                            <textarea name="task_result" rows="2" class="rounded-md border border-paw-line px-3 py-2" required maxlength="2000"></textarea>
                                        </label>
                                        <button type="submit" class="action action--primary action--compact w-full">
                                            <x-lucide-check class="icon icon--sm" aria-hidden="true" />
                                            <span>Complete</span>
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted sm:col-span-2">No volunteer tasks yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h2 class="text-xl font-bold">Volunteers</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($volunteers as $volunteer)
                                <article class="rounded-md border border-paw-line bg-white p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="font-bold">{{ $volunteer['display_name'] }}</h3>
                                        <span class="text-xs font-semibold text-paw-leaf">{{ $volunteer['status_label'] }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-paw-muted">{{ implode(' · ', $volunteer['capabilities']) ?: 'Available for general coordination' }}</p>
                                </article>
                            @empty
                                <p class="text-sm text-paw-muted">No volunteers have joined.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h2 class="text-xl font-bold">Alert delivery</h2>
                        <div class="mt-4 grid gap-3">
                            @forelse ($alerts as $alert)
                                <article class="rounded-md border border-paw-line bg-white p-4">
                                    <div class="flex items-center justify-between gap-3"><h3 class="font-bold">{{ $alert['kind'] }}</h3><span class="text-xs font-semibold">{{ ucfirst($alert['status']) }}</span></div>
                                    <p class="mt-2 text-sm text-paw-muted">{{ $alert['region'] }} · {{ $alert['radius_km'] }} km</p>
                                    <p class="mt-2 text-xs font-semibold">{{ $alert['recipient_count'] }} recipients</p>
                                </article>
                            @empty
                                <p class="text-sm text-paw-muted">No alert records.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <aside class="grid content-start gap-5 xl:sticky xl:top-24 xl:self-start">
                <section class="rounded-md border border-paw-line bg-white p-4">
                    <div class="flex items-center gap-2">
                        <x-lucide-lock-keyhole class="size-5 text-paw-leaf" aria-hidden="true" />
                        <h2 class="font-bold">Private case details</h2>
                    </div>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div><dt class="text-paw-muted">Exact point</dt><dd class="mt-1 font-semibold">{{ $search_case['exact_location']['latitude'] ?? 'Not set' }}, {{ $search_case['exact_location']['longitude'] ?? 'Not set' }}</dd></div>
                        @if ($search_case['exact_location']['note'] ?? null)<div><dt class="text-paw-muted">Location note</dt><dd class="mt-1 font-semibold">{{ $search_case['exact_location']['note'] }}</dd></div>@endif
                        @if ($search_case['hidden_marks'])<div><dt class="text-paw-muted">Hidden mark</dt><dd class="mt-1 font-semibold">{{ $search_case['hidden_marks'] }}</dd></div>@endif
                        <div><dt class="text-paw-muted">Protected contact</dt><dd class="mt-1 font-semibold">{{ $search_case['contact_details']['channel'] ?? 'platform' }} · {{ $search_case['contact_details']['value'] ?? 'owner account' }}</dd></div>
                    </dl>
                </section>

                <details open class="rounded-md border border-paw-line bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-2 p-4 font-bold">
                        <x-lucide-radio-tower class="size-5 text-paw-coral" aria-hidden="true" />
                        Publish update
                    </summary>
                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <input type="hidden" name="action" value="publish-update">
                        <label class="grid gap-1 text-sm font-semibold">Title<input name="update_title" class="rounded-md border border-paw-line px-3 py-2" required maxlength="160"></label>
                        <label class="grid gap-1 text-sm font-semibold">Update<textarea name="update_body" rows="3" class="rounded-md border border-paw-line px-3 py-2" maxlength="2000"></textarea></label>
                        <label class="grid gap-1 text-sm font-semibold">Public area<input name="update_area" class="rounded-md border border-paw-line px-3 py-2" maxlength="160"></label>
                        <button type="submit" class="action action--primary w-full"><x-lucide-send class="icon" aria-hidden="true" /><span>Publish</span></button>
                    </form>
                </details>

                <details class="rounded-md border border-paw-line bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-2 p-4 font-bold">
                        <x-lucide-grid-2x2-plus class="size-5 text-paw-leaf" aria-hidden="true" />
                        Add sector
                    </summary>
                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <input type="hidden" name="action" value="create-sector">
                        <div class="grid grid-cols-[6rem_1fr] gap-2">
                            <label class="grid gap-1 text-sm font-semibold">Code<input name="sector_code" class="min-w-0 rounded-md border border-paw-line px-3 py-2" required maxlength="30" placeholder="A1"></label>
                            <label class="grid gap-1 text-sm font-semibold">Label<input name="sector_label" class="min-w-0 rounded-md border border-paw-line px-3 py-2" required maxlength="120"></label>
                        </div>
                        <label class="grid gap-1 text-sm font-semibold">Priority<select name="sector_priority" class="rounded-md border border-paw-line px-3 py-2"><option value="1">High</option><option value="2" selected>Normal</option><option value="3">Low</option></select></label>
                        <label class="grid gap-1 text-sm font-semibold">Safety notes<textarea name="sector_risk_notes" rows="2" class="rounded-md border border-paw-line px-3 py-2" maxlength="1000"></textarea></label>
                        <label class="grid gap-1 text-sm font-semibold">Access notes<textarea name="sector_access_notes" rows="2" class="rounded-md border border-paw-line px-3 py-2" maxlength="1000"></textarea></label>
                        <button type="submit" class="action action--surface w-full"><x-lucide-plus class="icon" aria-hidden="true" /><span>Create sector</span></button>
                    </form>
                </details>

                <details class="rounded-md border border-paw-line bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-2 p-4 font-bold">
                        <x-lucide-list-plus class="size-5 text-paw-leaf" aria-hidden="true" />
                        Add task
                    </summary>
                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <input type="hidden" name="action" value="create-task">
                        <label class="grid gap-1 text-sm font-semibold">Task type<select name="task_type" class="rounded-md border border-paw-line px-3 py-2">@forelse ($task_types as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@empty<option disabled>No types</option>@endforelse</select></label>
                        <label class="grid gap-1 text-sm font-semibold">Sector<select name="sector_id" class="rounded-md border border-paw-line px-3 py-2"><option value="">No sector</option>@forelse ($sectors as $sector)<option value="{{ $sector['id'] }}">{{ $sector['code'] }} · {{ $sector['label'] }}</option>@empty<option disabled>No sectors yet</option>@endforelse</select></label>
                        <label class="grid gap-1 text-sm font-semibold">Title<input name="task_title" class="rounded-md border border-paw-line px-3 py-2" required maxlength="140"></label>
                        <label class="grid gap-1 text-sm font-semibold">Instructions<textarea name="task_description" rows="3" class="rounded-md border border-paw-line px-3 py-2" required maxlength="2000"></textarea></label>
                        <label class="grid gap-1 text-sm font-semibold">Safety<select name="safety_level" class="rounded-md border border-paw-line px-3 py-2"><option value="standard">Standard</option><option value="pair-required">Pair required</option><option value="specialist-only">Specialist only</option><option value="dangerous">Dangerous area</option></select></label>
                        <button type="submit" class="action action--surface w-full"><x-lucide-plus class="icon" aria-hidden="true" /><span>Create task</span></button>
                    </form>
                </details>

                <details class="rounded-md border border-paw-coral/40 bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-2 p-4 font-bold">
                        <x-lucide-circle-dot-dashed class="size-5 text-paw-coral" aria-hidden="true" />
                        Change search status
                    </summary>
                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <input type="hidden" name="action" value="update-status">
                        <label class="grid gap-1 text-sm font-semibold">Status<select name="status" class="rounded-md border border-paw-line px-3 py-2">@forelse ($statuses as $value => $label)<option value="{{ $value }}" @selected($search_case['status'] === $value)>{{ $label }}</option>@empty<option disabled>No statuses</option>@endforelse</select></label>
                        <label class="grid gap-1 text-sm font-semibold">Public note<textarea name="status_note" rows="3" class="rounded-md border border-paw-line px-3 py-2" maxlength="1500"></textarea></label>
                        <label class="flex items-start gap-2 text-xs"><input type="checkbox" name="return_confirmed" value="1" class="mt-0.5"><span>When choosing returned or returned home, I confirm the animal is safe and urgent processes can stop.</span></label>
                        <button type="submit" class="action action--primary w-full"><x-lucide-refresh-cw class="icon" aria-hidden="true" /><span>Update status</span></button>
                    </form>
                </details>
            </aside>
        </div>
    </div>
</x-app-shell>
