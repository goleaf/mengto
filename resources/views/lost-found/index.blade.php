<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase text-paw-coral">Lost & found</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Active local searches</h1>
                <p class="mt-3 max-w-2xl leading-7 text-paw-muted">
                    Report a sighting, join a coordinated task, or help a found animal reach a verified owner without exposing private addresses.
                </p>
            </div>
            <x-action-control label="Report an animal" icon="siren" variant="primary" :href="route('lost-found.create')" />
        </header>

        @if (session('feedback'))
            <div class="flex items-start gap-3 rounded-md border border-paw-leaf/30 bg-paw-mint p-4 text-sm font-semibold text-paw-leaf" role="status">
                <x-lucide-circle-check-big class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                {{ session('feedback') }}
            </div>
        @endif

        <section class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line md:grid-cols-5" aria-label="Search activity summary">
            @forelse ([
                ['label' => 'Active', 'value' => $stats['active'], 'icon' => 'siren'],
                ['label' => 'Missing', 'value' => $stats['lost'], 'icon' => 'scan-search'],
                ['label' => 'Found', 'value' => $stats['found'], 'icon' => 'shield-check'],
                ['label' => 'Sightings', 'value' => $stats['sightings'], 'icon' => 'map-pin-check'],
                ['label' => 'Volunteers', 'value' => $stats['volunteers'], 'icon' => 'users-round'],
            ] as $stat)
                <div class="flex items-center gap-3 bg-white p-4">
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" class="size-5 shrink-0 text-paw-leaf" aria-hidden="true" />
                    <div>
                        <strong class="block text-xl">{{ $stat['value'] }}</strong>
                        <span class="text-xs text-paw-muted">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">Search statistics are unavailable.</p>
            @endforelse
        </section>

        <form method="GET" action="{{ route('lost-found.index') }}" class="grid gap-4 border-y border-paw-line py-5" role="search">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,2fr)_repeat(3,minmax(10rem,1fr))]">
                <label class="grid gap-1 text-sm font-semibold">
                    Search
                    <span class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3">
                        <x-lucide-search class="size-4 text-paw-muted" aria-hidden="true" />
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 outline-none" placeholder="Name, color, area, or code">
                    </span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Report type
                    <select name="type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Missing and found</option>
                        @forelse ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No report types</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Status
                    <select name="status" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Any status</option>
                        @forelse ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No statuses</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Animal
                    <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Every species</option>
                        @forelse ($species_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No species available</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto]">
                <label class="grid gap-1 text-sm font-semibold">
                    City
                    <input name="city" value="{{ $filters['city'] ?? '' }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Vilnius">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Sort
                    <select name="sort" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($sort_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest-sighting') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No sort options</option>
                        @endforelse
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="action action--primary action--compact">
                        <x-lucide-sliders-horizontal class="icon icon--sm" aria-hidden="true" />
                        <span>Apply</span>
                    </button>
                    <a href="{{ route('lost-found.index') }}" class="action action--surface action--compact" title="Clear filters">
                        <x-lucide-rotate-ccw class="icon icon--sm" aria-hidden="true" />
                        <span class="sr-only">Clear filters</span>
                    </a>
                </div>
            </div>
        </form>

        <div class="grid gap-8 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,.65fr)]">
            <section aria-labelledby="search-results-title">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 id="search-results-title" class="text-2xl font-bold">Search reports</h2>
                    <span class="text-sm text-paw-muted">Newest verified activity first</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @forelse ($search_cases as $searchCase)
                        <x-search-case-card :search-case="$searchCase" />
                    @empty
                        <div class="grid min-h-60 place-items-center rounded-md border border-dashed border-paw-line bg-white p-8 text-center md:col-span-2">
                            <div>
                                <x-lucide-search-x class="mx-auto size-9 text-paw-muted" aria-hidden="true" />
                                <h3 class="mt-3 font-bold">No matching reports</h3>
                                <p class="mt-1 text-sm text-paw-muted">Try a wider area or clear one of the filters.</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-5">
                    {{ $search_cases->links() }}
                </div>
            </section>

            <aside class="xl:sticky xl:top-24 xl:self-start">
                <x-search-map :markers="$map_markers" title="Visible search area" compact />
                <div class="mt-5 border-t border-paw-line pt-5">
                    <h2 class="font-bold">See an animal?</h2>
                    <p class="mt-2 text-sm leading-6 text-paw-muted">
                        Open the matching card and send the actual observation time, general area, direction, and a photo when it is safe.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</x-app-shell>
