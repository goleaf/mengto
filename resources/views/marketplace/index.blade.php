<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase text-paw-leaf">Community marketplace</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Useful things and trusted pet services</h1>
                <p class="mt-3 max-w-2xl leading-7 text-paw-muted">
                    Buy, exchange, rehome, or book without exposing your phone number or home address before both sides agree.
                </p>
            </div>
            <x-action-control label="Create listing" icon="badge-plus" variant="primary" :href="route('marketplace.create')" />
        </header>

        <section class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line lg:grid-cols-4" aria-label="Marketplace summary">
            @forelse ([
                ['label' => 'Available', 'value' => $stats['available'], 'icon' => 'store'],
                ['label' => 'For adoption', 'value' => $stats['adoption'], 'icon' => 'heart-handshake'],
                ['label' => 'Free', 'value' => $stats['free'], 'icon' => 'gift'],
                ['label' => 'Cities', 'value' => $stats['cities'], 'icon' => 'map-pin'],
            ] as $stat)
                <div class="flex items-center gap-3 bg-white p-4">
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" class="size-5 shrink-0 text-paw-leaf" aria-hidden="true" />
                    <div>
                        <strong class="block text-xl">{{ $stat['value'] }}</strong>
                        <span class="text-xs text-paw-muted">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">Marketplace statistics are unavailable.</p>
            @endforelse
        </section>

        <form method="GET" action="{{ route('marketplace.index') }}" class="grid gap-4 border-y border-paw-line py-5" role="search">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,2fr)_repeat(3,minmax(10rem,1fr))]">
                <label class="grid gap-1 text-sm font-semibold">
                    Search
                    <span class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3">
                        <x-lucide-search class="size-4 text-paw-muted" aria-hidden="true" />
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 outline-none" placeholder="Item, service, city, or category">
                    </span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Listing type
                    <select name="type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">All types</option>
                        @forelse ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No listing types</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Category
                    <select name="category" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">All categories</option>
                        @forelse ($categories as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No categories</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Pet
                    <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Every pet type</option>
                        @forelse ($species_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No pet types</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <label class="grid gap-1 text-sm font-semibold">
                    City
                    <input name="city" value="{{ $filters['city'] ?? '' }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Vilnius">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Handover
                    <select name="delivery" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Any option</option>
                        @forelse ($delivery_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['delivery'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No handover options</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Price
                    <select name="price" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($price_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['price'] ?? 'any') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No price filters</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Sort
                    <select name="sort" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($sort_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No sort options</option>
                        @endforelse
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="action action--primary action--compact flex-1">
                        <x-lucide-sliders-horizontal class="icon icon--sm" aria-hidden="true" />
                        <span>Apply</span>
                    </button>
                    <a href="{{ route('marketplace.index') }}" class="action action--surface action--compact" title="Clear filters">
                        <x-lucide-rotate-ccw class="icon icon--sm" aria-hidden="true" />
                        <span class="sr-only">Clear filters</span>
                    </a>
                </div>
            </div>
        </form>

        <section aria-labelledby="listings-heading">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="listings-heading" class="text-xl font-bold">Available now</h2>
                    <p class="mt-1 text-sm text-paw-muted">Community status is not a guarantee. Inspect items and verify services before payment.</p>
                </div>
                <span class="inline-flex items-center gap-2 text-sm font-semibold text-paw-muted">
                    <x-lucide-shield-check class="size-4 text-paw-leaf" aria-hidden="true" />
                    Platform-only contact
                </span>
            </div>

            <div class="market-grid">
                @forelse ($listings as $listing)
                    <x-listing-card :listing="$listing" />
                @empty
                    <div class="market-empty">
                        <x-lucide-search-x class="size-8 text-paw-muted" aria-hidden="true" />
                        <h3 class="mt-3 text-xl font-bold">No exact match yet</h3>
                        <p class="mt-2 max-w-xl text-paw-muted">Remove one filter, search a nearby city, or create a clear request in the forum.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $listings->links() }}</div>
        </section>
    </div>
</x-app-shell>
