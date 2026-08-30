<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <x-page-header
            :eyebrow="__('ui.lost_found')"
            :title="__('ui.active_local_searches')"
            :description="__('ui.report_a_sighting_join_a_coordinated_task_or_help_a_found_animal_reach_a_verified_owner_without_exposing_private_addresses')"
            heading-id="lost-found-heading"
            :action-label="__('ui.report_an_animal')"
            action-icon="siren"
            :action-href="route('lost-found.create')"
            data-section="lost-found-header"
        />

        @if (session('feedback'))
            <div class="flex items-start gap-3 rounded-md border border-paw-leaf/30 bg-paw-mint p-4 text-sm font-semibold text-paw-leaf" role="status">
                <x-ui-icon name="circle-check-big" size="lg" class="mt-0.5 shrink-0" />
                {{ session('feedback') }}
            </div>
        @endif

        <section data-lost-found-stats class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line md:grid-cols-5" aria-label="{{ __('ui.search_activity_summary') }}">
            @forelse ([
                ['label' => __('ui.active'), 'value' => $stats['active'], 'icon' => 'siren'],
                ['label' => __('ui.missing'), 'value' => $stats['lost'], 'icon' => 'scan-search'],
                ['label' => __('ui.found'), 'value' => $stats['found'], 'icon' => 'shield-check'],
                ['label' => __('ui.sightings'), 'value' => $stats['sightings'], 'icon' => 'map-pin-check'],
                ['label' => __('ui.volunteers'), 'value' => $stats['volunteers'], 'icon' => 'users-round'],
            ] as $stat)
                <div
                    data-lost-found-stat
                    @class([
                        'flex items-center gap-3 bg-white p-4',
                        'col-span-2 md:col-span-1' => $loop->last,
                    ])
                >
                    <x-ui-icon size="lg" :name="$stat['icon']" class="shrink-0 text-paw-leaf" />
                    <div>
                        <strong class="block text-xl">{{ $stat['value'] }}</strong>
                        <span class="text-xs text-paw-muted">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">{{ __('ui.search_statistics_are_unavailable') }}</p>
            @endforelse
        </section>

        <form method="GET" action="{{ route('lost-found.index') }}" class="grid gap-4 border-y border-paw-line py-5" role="search">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,2fr)_repeat(3,minmax(10rem,1fr))]">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.search') }}
                    <span class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3">
                        <x-ui-icon name="search" size="sm" class="text-paw-muted" />
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 outline-none" placeholder="{{ __('ui.name_color_area_or_code') }}">
                    </span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.report_type') }}
                    <select name="type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.missing_and_found') }}</option>
                        @forelse ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_report_types') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.status') }}
                    <select name="status" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_status') }}</option>
                        @forelse ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_statuses') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.animal') }}
                    <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.every_species') }}</option>
                        @forelse ($species_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_species_available') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto]">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.city') }}
                    <input name="city" value="{{ $filters['city'] ?? '' }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.vilnius') }}">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.sort') }}
                    <select name="sort" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($sort_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest-sighting') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_sort_options') }}</option>
                        @endforelse
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="action action--primary action--compact">
                        <x-ui-icon name="sliders-horizontal" size="sm" />
                        <span>{{ __('ui.apply') }}</span>
                    </button>
                    <a href="{{ route('lost-found.index') }}" class="action action--surface action--icon" title="{{ __('ui.clear_filters') }}">
                        <x-ui-icon name="rotate-ccw" size="sm" />
                        <span class="sr-only">{{ __('ui.clear_filters') }}</span>
                    </a>
                </div>
            </div>
        </form>

        <div class="grid gap-8 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,.65fr)]">
            <section aria-labelledby="search-results-title">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 id="search-results-title" class="text-2xl font-bold">{{ __('ui.search_reports') }}</h2>
                    <span data-lost-found-results-order class="text-sm text-paw-muted">{{ __('ui.newest_verified_activity_first') }}</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @forelse ($search_cases as $searchCase)
                        <x-search-case-card :search-case="$searchCase" />
                    @empty
                        <div class="grid min-h-60 place-items-center rounded-md border border-dashed border-paw-line bg-white p-8 text-center md:col-span-2">
                            <div>
                                <x-ui-icon name="search-x" size="3xl" class="mx-auto text-paw-muted" />
                                <h3 class="mt-3 font-bold">{{ __('ui.no_matching_reports') }}</h3>
                                <p class="mt-1 text-sm text-paw-muted">{{ __('ui.try_a_wider_area_or_clear_one_of_the_filters') }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-5">
                    {{ $search_cases->links() }}
                </div>
            </section>

            <aside class="xl:sticky xl:top-24 xl:self-start">
                <x-search-map :markers="$map_markers" title="{{ __('ui.visible_search_area') }}" compact />
                <div class="mt-5 border-t border-paw-line pt-5">
                    <h2 data-lost-found-guidance-title class="font-bold">{{ __('ui.see_an_animal') }}</h2>
                    <p data-lost-found-guidance-copy class="mt-2 text-sm leading-6 text-paw-muted">
                        {{ __('ui.open_the_matching_card_and_send_the_actual_observation_time_general_area_direction_and_a_photo_when_it_is_safe') }}
                    </p>
                </div>
            </aside>
        </div>
    </div>
</x-app-shell>
