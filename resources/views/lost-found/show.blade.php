<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid grid-cols-[minmax(0,1fr)] gap-7">
        <header class="grid gap-5 border-b border-paw-line pb-6">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route('lost-found.index') }}" class="inline-flex items-center gap-2 font-semibold text-paw-leaf">
                    <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                    Lost & found
                </a>
                <span class="text-paw-line">/</span>
                <span class="font-semibold text-paw-muted">{{ $search_case['public_code'] }}</span>
            </div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded bg-paw-mint px-2 py-1 text-xs font-bold text-paw-leaf">
                            <x-dynamic-component :component="'lucide-'.$search_case['type_icon']" class="size-3.5" aria-hidden="true" />
                            {{ $search_case['type_label'] }}
                        </span>
                        <span class="rounded px-2 py-1 text-xs font-bold {{ $search_case['urgent'] ? 'bg-red-100 text-red-800' : 'bg-paw-sun/60 text-paw-ink' }}">
                            {{ $search_case['status_label'] }}
                        </span>
                        <span class="text-xs font-semibold text-paw-muted">Updated {{ $search_case['latest_update_label'] }}</span>
                    </div>
                    <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $search_case['pet_name'] }}</h1>
                    <p class="mt-2 text-lg text-paw-muted">
                        {{ $search_case['species_label'] }}
                        @if ($search_case['breed'])
                            · {{ $search_case['breed'] }}
                        @endif
                        · {{ $search_case['color'] }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if ($can_manage)
                        <x-action-control label="Coordinate" icon="map" variant="primary" :href="route('lost-found.coordinate', $search_case['slug'])" />
                    @endif
                    <x-action-control label="Poster" icon="printer" variant="surface" :href="$search_case['poster_url']" />
                    <x-action-control label="Share" icon="share-2" variant="surface" :href="route('share.show', 'lost-found-'.$search_case['slug'])" />
                </div>
            </div>
        </header>

        @if (session('feedback'))
            <div class="flex items-start gap-3 rounded-md border border-paw-leaf/30 bg-paw-mint p-4 text-sm font-semibold text-paw-leaf" role="status">
                <x-lucide-circle-check-big class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                {{ session('feedback') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-900" role="alert">
                <div class="flex items-center gap-2 font-bold">
                    <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                    The form needs attention
                </div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>No validation details are available.</li>
                    @endforelse
                </ul>
            </div>
        @endif

        @if ($search_case['urgent'])
            <section class="grid gap-4 rounded-md border-2 border-paw-coral bg-red-50 p-5 sm:grid-cols-[auto_1fr]" aria-labelledby="urgent-instruction-title">
                <x-lucide-siren class="size-7 text-paw-coral" aria-hidden="true" />
                <div>
                    <h2 id="urgent-instruction-title" class="text-lg font-bold">Do not chase or surround {{ $search_case['pet_name'] }}</h2>
                    <p class="mt-1 leading-7 text-paw-muted">
                        {{ $search_case['approach_instructions'] ?: 'Stay at a safe distance, note the direction, and report the observation time.' }}
                    </p>
                    @if ($search_case['avoid_instructions'])
                        <p class="mt-2 text-sm font-semibold text-paw-coral">{{ $search_case['avoid_instructions'] }}</p>
                    @endif
                </div>
            </section>
        @endif

        <section class="grid grid-cols-[minmax(0,1fr)] gap-7 lg:grid-cols-[minmax(0,1.05fr)_minmax(20rem,.95fr)]" aria-label="Animal and report details">
            <div class="overflow-hidden rounded-md border border-paw-line bg-paw-mint">
                @if ($search_case['cover_url'])
                    <img src="{{ $search_case['cover_url'] }}" alt="{{ $search_case['pet_name'] }}, {{ strtolower($search_case['species_label']) }}, {{ $search_case['color'] }}" class="aspect-[4/3] size-full object-cover">
                @else
                    <div class="grid aspect-[4/3] place-items-center">
                        <x-dynamic-component :component="'lucide-'.$search_case['type_icon']" class="size-20 text-paw-leaf" aria-hidden="true" />
                    </div>
                @endif
            </div>

            <div class="grid content-start gap-5">
                <div>
                    <h2 class="text-xl font-bold">Identification</h2>
                    <p class="mt-3 leading-7">{{ $search_case['description'] }}</p>
                </div>

                <dl class="grid gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line sm:grid-cols-2">
                    @forelse ([
                        ['label' => 'Last area', 'value' => $search_case['last_seen_area']],
                        ['label' => 'Last seen', 'value' => $search_case['last_seen_label']],
                        ['label' => 'Size', 'value' => $search_case['size_label']],
                        ['label' => 'Age', 'value' => $search_case['age_label']],
                        ['label' => 'Sex', 'value' => $search_case['sex']],
                        ['label' => 'Microchip', 'value' => $search_case['microchip_label']],
                    ] as $detail)
                        @if ($detail['value'])
                            <div class="bg-white p-3">
                                <dt class="text-xs font-bold uppercase text-paw-muted">{{ $detail['label'] }}</dt>
                                <dd class="mt-1 font-semibold">{{ $detail['value'] }}</dd>
                            </div>
                        @endif
                    @empty
                        <p class="bg-white p-3 text-sm text-paw-muted">No identification details.</p>
                    @endforelse
                </dl>

                @if ($search_case['distinctive_marks'])
                    <div class="border-l-4 border-paw-sun pl-4">
                        <h3 class="font-bold">Visible identifying marks</h3>
                        <p class="mt-1 text-sm leading-6 text-paw-muted">{{ $search_case['distinctive_marks'] }}</p>
                    </div>
                @endif

                @if ($search_case['health_notice'])
                    <div class="flex items-start gap-3 rounded-md border border-paw-coral/30 bg-red-50 p-4">
                        <x-lucide-heart-pulse class="mt-0.5 size-5 shrink-0 text-paw-coral" aria-hidden="true" />
                        <p class="text-sm font-semibold">{{ $search_case['health_notice'] }}</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="grid grid-cols-[minmax(0,1fr)] gap-6 border-y border-paw-line py-7 xl:grid-cols-[minmax(0,1.25fr)_minmax(20rem,.75fr)]">
            <x-search-map :markers="$map_markers" :title="'Search map for '.$search_case['pet_name']" />

            <div class="grid content-start gap-5">
                <div>
                    <p class="text-xs font-bold uppercase text-paw-coral">Latest update</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ $search_case['status_label'] }}</h2>
                    <p class="mt-2 leading-7 text-paw-muted">{{ $search_case['latest_update'] }}</p>
                </div>
                <dl class="grid gap-3 text-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-paw-line pb-3">
                        <dt class="text-paw-muted">Alert radius</dt>
                        <dd class="font-bold">{{ $search_case['notification_radius_km'] }} km</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-b border-paw-line pb-3">
                        <dt class="text-paw-muted">Reached through platform</dt>
                        <dd class="font-bold">{{ $alert_reach }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-b border-paw-line pb-3">
                        <dt class="text-paw-muted">Contact</dt>
                        <dd class="inline-flex items-center gap-1 font-bold text-paw-leaf">
                            <x-lucide-shield-check class="size-4" aria-hidden="true" />
                            Protected
                        </dd>
                    </div>
                </dl>
                <p class="text-xs leading-5 text-paw-muted">
                    Map points are generalized. Exact addresses, hidden marks, and volunteer locations are not public.
                </p>
            </div>
        </section>

        <div class="grid grid-cols-[minmax(0,1fr)] gap-8 xl:grid-cols-[minmax(0,1.25fr)_minmax(22rem,.75fr)]">
            <div class="grid min-w-0 content-start gap-8">
                <section id="search-timeline" aria-labelledby="timeline-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="timeline-title" class="text-2xl font-bold">Search timeline</h2>
                        <span class="text-sm text-paw-muted">{{ count($updates) }} updates</span>
                    </div>
                    <ol class="mt-4 grid gap-3">
                        @forelse ($updates as $update)
                            <li class="grid grid-cols-[auto_1fr] gap-3 rounded-md border border-paw-line bg-white p-4">
                                <span class="grid size-9 place-items-center rounded-full bg-paw-mint text-paw-leaf">
                                    <x-lucide-radio-tower class="size-4" aria-hidden="true" />
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h3 class="font-bold">{{ $update['title'] }}</h3>
                                        <time class="text-xs font-semibold text-paw-muted">{{ $update['occurred_label'] }}</time>
                                    </div>
                                    @if ($update['body'])
                                        <p class="mt-1 text-sm leading-6 text-paw-muted">{{ $update['body'] }}</p>
                                    @endif
                                    @if ($update['public_area'])
                                        <p class="mt-2 inline-flex items-center gap-1 text-xs font-semibold">
                                            <x-lucide-map-pin class="size-3.5 text-paw-coral" aria-hidden="true" />
                                            {{ $update['public_area'] }}
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted">No public updates yet.</li>
                        @endforelse
                    </ol>
                </section>

                <section aria-labelledby="confirmed-sightings-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="confirmed-sightings-title" class="text-2xl font-bold">Confirmed sightings</h2>
                        <span class="text-sm text-paw-muted">{{ count($sightings) }} verified</span>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @forelse ($sightings as $sighting)
                            <article class="rounded-md border border-paw-line bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold">{{ $sighting['public_area'] }}</h3>
                                        <p class="mt-1 text-xs font-semibold text-paw-muted">{{ $sighting['observed_label'] }}</p>
                                    </div>
                                    <span class="rounded bg-paw-mint px-2 py-1 text-xs font-bold text-paw-leaf">{{ $sighting['status_label'] }}</span>
                                </div>
                                @if ($sighting['notes'])
                                    <p class="mt-3 text-sm leading-6 text-paw-muted">{{ $sighting['notes'] }}</p>
                                @endif
                                <dl class="mt-3 grid gap-1 text-xs">
                                    <div class="flex gap-2"><dt class="text-paw-muted">Confidence</dt><dd class="font-semibold">{{ $sighting['confidence'] }}</dd></div>
                                    <div class="flex gap-2"><dt class="text-paw-muted">Contact</dt><dd class="font-semibold">{{ $sighting['contact_status'] }}</dd></div>
                                </dl>
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted sm:col-span-2">No sighting has been confirmed yet.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="tasks-title">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="tasks-title" class="text-2xl font-bold">Open volunteer tasks</h2>
                        <span class="text-sm text-paw-muted">{{ count($tasks) }} available</span>
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
                                    @if ($task['due_label'])<span>Due {{ $task['due_label'] }}</span>@endif
                                </div>
                                @if ($can_volunteer && $task['status'] === 'open')
                                    <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="action" value="claim-task">
                                        <input type="hidden" name="task_id" value="{{ $task['id'] }}">
                                        <button type="submit" class="action action--surface action--compact w-full">
                                            <x-lucide-hand class="icon icon--sm" aria-hidden="true" />
                                            <span>Claim task</span>
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="rounded-md border border-dashed border-paw-line p-6 text-sm text-paw-muted sm:col-span-2">There are no open public tasks.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="organizations-title">
                    <h2 id="organizations-title" class="text-2xl font-bold">Nearby clinics and shelters</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @forelse ($organizations as $organization)
                            <article class="flex items-start gap-3 rounded-md border border-paw-line bg-white p-4">
                                <span class="grid size-10 shrink-0 place-items-center rounded bg-paw-mint text-paw-leaf">
                                    <x-dynamic-component :component="'lucide-'.$organization['icon']" class="size-5" aria-hidden="true" />
                                </span>
                                <div class="min-w-0">
                                    <h3 class="font-bold">{{ $organization['name'] }}</h3>
                                    <p class="mt-1 text-sm text-paw-muted">{{ $organization['category'] }} · {{ $organization['general_location'] }}</p>
                                    <p class="mt-2 text-xs font-semibold {{ $organization['emergency'] ? 'text-paw-coral' : 'text-paw-leaf' }}">{{ $organization['open_label'] }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No verified local organizations are listed.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="grid min-w-0 grid-cols-[minmax(0,1fr)] content-start gap-5 xl:sticky xl:top-24 xl:self-start">
                @if ($can_submit_sighting)
                    <details open class="rounded-md border border-paw-line bg-white">
                        <summary class="flex cursor-pointer list-none items-center gap-3 p-4 font-bold">
                            <span class="grid size-9 place-items-center rounded bg-paw-coral text-white">
                                <x-lucide-eye class="size-5" aria-hidden="true" />
                            </span>
                            I saw this animal
                        </summary>
                        <form method="POST" action="{{ route('lost-found.sightings.store', $search_case['slug']) }}" enctype="multipart/form-data" class="grid gap-4 border-t border-paw-line p-4">
                            @csrf
                            <input type="hidden" name="idempotency_key" value="{{ $idempotency_key }}">
                            <label class="grid gap-1 text-sm font-semibold">
                                Observation time
                                <input type="datetime-local" name="observed_at" value="{{ old('observed_at', now()->format('Y-m-d\TH:i')) }}" class="rounded-md border border-paw-line px-3 py-2" required>
                            </label>
                            <label class="grid gap-1 text-sm font-semibold">
                                Time accuracy
                                <select name="time_accuracy" class="rounded-md border border-paw-line px-3 py-2">
                                    <option value="exact">Exact</option>
                                    <option value="within-30-minutes">Within 30 minutes</option>
                                    <option value="morning">Morning</option>
                                    <option value="afternoon">Afternoon</option>
                                    <option value="evening">Evening</option>
                                    <option value="night">Night</option>
                                    <option value="unknown">Unknown</option>
                                </select>
                            </label>
                            <label class="grid gap-1 text-sm font-semibold">
                                General area
                                <input name="public_area" value="{{ old('public_area') }}" class="rounded-md border border-paw-line px-3 py-2" required maxlength="160">
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="grid gap-1 text-sm font-semibold">
                                    Latitude
                                    <input type="number" step="0.000001" name="latitude" value="{{ old('latitude', '54.683400') }}" class="min-w-0 rounded-md border border-paw-line px-3 py-2" required>
                                </label>
                                <label class="grid gap-1 text-sm font-semibold">
                                    Longitude
                                    <input type="number" step="0.000001" name="longitude" value="{{ old('longitude', '25.236800') }}" class="min-w-0 rounded-md border border-paw-line px-3 py-2" required>
                                </label>
                            </div>
                            <label class="grid gap-1 text-sm font-semibold">
                                Direction
                                <input name="direction" value="{{ old('direction') }}" class="rounded-md border border-paw-line px-3 py-2" maxlength="100">
                            </label>
                            <label class="grid gap-1 text-sm font-semibold">
                                Confidence
                                <select name="confidence" class="rounded-md border border-paw-line px-3 py-2">
                                    @forelse ($confidence_options as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>No options</option>
                                    @endforelse
                                </select>
                            </label>
                            <label class="grid gap-1 text-sm font-semibold">
                                Contact
                                <select name="contact_status" class="rounded-md border border-paw-line px-3 py-2">
                                    @forelse ($contact_statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @empty
                                        <option disabled>No options</option>
                                    @endforelse
                                </select>
                            </label>
                            <label class="grid gap-1 text-sm font-semibold">
                                Notes
                                <textarea name="notes" rows="3" class="rounded-md border border-paw-line px-3 py-2" maxlength="2000">{{ old('notes') }}</textarea>
                            </label>
                            <label class="grid gap-1 text-sm font-semibold">
                                Photo
                                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="rounded-md border border-paw-line px-3 py-2">
                            </label>
                            <label class="flex items-start gap-2 text-xs">
                                <input type="checkbox" name="safety_acknowledged" value="1" class="mt-0.5" required>
                                <span>I am in a safe place and will not chase, enter traffic, or approach a dangerous animal.</span>
                            </label>
                            <button type="submit" class="action action--primary w-full">
                                <x-lucide-map-pin-plus class="icon" aria-hidden="true" />
                                <span>Send sighting</span>
                            </button>
                        </form>
                    </details>
                @endif

                @if ($can_volunteer)
                    <details class="rounded-md border border-paw-line bg-white">
                        <summary class="flex cursor-pointer list-none items-center gap-3 p-4 font-bold">
                            <span class="grid size-9 place-items-center rounded bg-paw-mint text-paw-leaf">
                                <x-lucide-hand-heart class="size-5" aria-hidden="true" />
                            </span>
                            Join the search
                        </summary>
                        <form method="POST" action="{{ route('lost-found.actions', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                            @csrf
                            <input type="hidden" name="action" value="join-search">
                            @forelse ($volunteer_capabilities as $value => $label)
                                <label class="flex items-start gap-2 text-sm">
                                    <input type="checkbox" name="capabilities[]" value="{{ $value }}" class="mt-0.5">
                                    <span>{{ $label }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-paw-muted">No volunteer roles are open.</p>
                            @endforelse
                            <button type="submit" class="action action--surface w-full">
                                <x-lucide-users-round class="icon" aria-hidden="true" />
                                <span>Join safely</span>
                            </button>
                        </form>
                    </details>
                @endif

                <details class="rounded-md border border-paw-line bg-white">
                    <summary class="flex cursor-pointer list-none items-center gap-3 p-4 font-bold">
                        <span class="grid size-9 place-items-center rounded bg-paw-sun/60">
                            <x-lucide-flag class="size-5" aria-hidden="true" />
                        </span>
                        Report a concern
                    </summary>
                    <form method="POST" action="{{ route('lost-found.reports.store', $search_case['slug']) }}" class="grid gap-3 border-t border-paw-line p-4">
                        @csrf
                        <label class="grid gap-1 text-sm font-semibold">
                            Reason
                            <select name="reason" class="rounded-md border border-paw-line px-3 py-2" required>
                                @forelse ($report_reasons as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @empty
                                    <option disabled>No report reasons</option>
                                @endforelse
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold">
                            Details
                            <textarea name="details" rows="3" class="rounded-md border border-paw-line px-3 py-2" maxlength="2500"></textarea>
                        </label>
                        <button type="submit" class="action action--surface w-full">
                            <x-lucide-shield-alert class="icon" aria-hidden="true" />
                            <span>Send to moderation</span>
                        </button>
                    </form>
                </details>
            </aside>
        </div>
    </div>
</x-app-shell>
