<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase text-paw-leaf">Verified professional community</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Find the right specialist for this pet</h1>
                <p class="mt-3 max-w-2xl leading-7 text-paw-muted">
                    Compare scope, species, independently checked credentials, availability, language, and price before sharing any private pet information.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.action-control label="Professional workspace" icon="briefcase-business" :href="route('experts.dashboard')" />
                <x-ui.action-control label="Create professional profile" icon="badge-plus" variant="primary" :href="route('experts.create')" />
            </div>
        </header>

        <section class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line lg:grid-cols-4" aria-label="Expert directory summary">
            @forelse ($stats as $stat)
                <div class="flex items-center gap-3 bg-white p-4">
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" class="size-5 shrink-0 text-paw-leaf" aria-hidden="true" />
                    <div>
                        <strong class="block text-xl">{{ $stat['value'] }}</strong>
                        <span class="text-xs text-paw-muted">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">Directory statistics are not available yet.</p>
            @endforelse
        </section>

        <form method="GET" action="{{ route('experts.index') }}" class="grid gap-4 border-y border-paw-line py-5" role="search">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,2fr)_repeat(3,minmax(10rem,1fr))]">
                <label class="grid gap-1 text-sm font-semibold">
                    Search
                    <span class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3">
                        <x-lucide-search class="size-4 text-paw-muted" aria-hidden="true" />
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 outline-none" placeholder="Name, skill, city, or approach">
                    </span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Specialist
                    <select name="type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">All specialist types</option>
                        @forelse ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No specialist types</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Species
                    <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Every species</option>
                        @forelse ($species_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No species options</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Problem
                    <select name="specialization" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">All areas</option>
                        @forelse ($specializations as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['specialization'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No specializations</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <label class="grid gap-1 text-sm font-semibold">
                    City
                    <input name="city" value="{{ $filters['city'] ?? '' }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Vilnius">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Format
                    <select name="format" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Any format</option>
                        @forelse ($formats as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['format'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No formats</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Language
                    <select name="language" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Any language</option>
                        @forelse ($languages as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['language'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No languages</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Availability
                    <select name="availability" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">Any availability</option>
                        @forelse ($availability_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['availability'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No availability options</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Sort
                    <select name="sort" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($sort_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'relevance') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No sorting options</option>
                        @endforelse
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="action action--primary action--compact flex-1">
                        <x-lucide-sliders-horizontal class="icon icon--sm" aria-hidden="true" />
                        <span>Apply</span>
                    </button>
                    <a href="{{ route('experts.index') }}" class="action action--surface action--compact" title="Clear filters">
                        <x-lucide-rotate-ccw class="icon icon--sm" aria-hidden="true" />
                        <span class="sr-only">Clear filters</span>
                    </a>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="verified" value="1" @checked((bool) ($filters['verified'] ?? false)) class="size-4 rounded border-paw-line text-paw-leaf">
                Show only profiles with a checked professional qualification
            </label>
        </form>

        <section aria-labelledby="directory-heading">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="directory-heading" class="text-xl font-bold">Matching professionals</h2>
                    <p class="mt-1 text-sm text-paw-muted">A verification badge explains what was checked. It is never a guarantee of outcome.</p>
                </div>
                <a href="{{ url('/places?category=emergency-vet') }}" class="inline-flex items-center gap-2 text-sm font-bold text-red-700 underline decoration-red-300 underline-offset-4">
                    <x-lucide-siren class="size-4" aria-hidden="true" />
                    Need urgent veterinary help?
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($experts as $expert)
                    <x-object.expert-card :expert="$expert" />
                @empty
                    <div class="md:col-span-2 xl:col-span-3">
                        <h3 class="text-xl font-bold">No exact match yet</h3>
                        <p class="mt-2 max-w-xl text-paw-muted">Remove one filter, try a nearby city, or browse newly verified specialists who accept online consultations.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $experts->links() }}</div>
        </section>
    </div>
</x-layout.app-shell>
