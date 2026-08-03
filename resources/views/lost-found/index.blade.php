<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <x-page-header
            :eyebrow="__('ui.lost_found_217c655848')"
            :title="__('ui.active_local_searches_a0b657fac3')"
            :description="__('ui.report_a_sighting_join_a_coordinated_task_or_e5e0bbe8c2')"
            heading-id="lost-found-heading"
            :action-label="__('ui.report_an_animal_6188a5d89e')"
            action-icon="siren"
            :action-href="route('lost-found.create')"
            data-section="lost-found-header"
        />

        @if (session('feedback'))
            <div class="flex items-start gap-3 rounded-md border border-paw-leaf/30 bg-paw-mint p-4 text-sm font-semibold text-paw-leaf" role="status">
                <x-lucide-circle-check-big class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                {{ session('feedback') }}
            </div>
        @endif

        <section class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line md:grid-cols-5" aria-label="{{ __('ui.search_activity_summary_b377289d94') }}">
            @forelse ([
                ['label' => __('ui.active_9234069589'), 'value' => $stats['active'], 'icon' => 'siren'],
                ['label' => __('ui.missing_6be36ca49e'), 'value' => $stats['lost'], 'icon' => 'scan-search'],
                ['label' => __('ui.found_b0ee315f4a'), 'value' => $stats['found'], 'icon' => 'shield-check'],
                ['label' => __('ui.sightings_4906ba1ea4'), 'value' => $stats['sightings'], 'icon' => 'map-pin-check'],
                ['label' => __('ui.volunteers_6ec733ad33'), 'value' => $stats['volunteers'], 'icon' => 'users-round'],
            ] as $stat)
                <div class="flex items-center gap-3 bg-white p-4">
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" class="size-5 shrink-0 text-paw-leaf" aria-hidden="true" />
                    <div>
                        <strong class="block text-xl">{{ $stat['value'] }}</strong>
                        <span class="text-xs text-paw-muted">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">{{ __('ui.search_statistics_are_unavailable_94eb5a5737') }}</p>
            @endforelse
        </section>

        <form method="GET" action="{{ route('lost-found.index') }}" class="grid gap-4 border-y border-paw-line py-5" role="search">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,2fr)_repeat(3,minmax(10rem,1fr))]">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.search_49c266baaa') }}
                    <span class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3">
                        <x-lucide-search class="size-4 text-paw-muted" aria-hidden="true" />
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 outline-none" placeholder="{{ __('ui.name_color_area_or_code_e3a84ea921') }}">
                    </span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.report_type_8c9986f3ba') }}
                    <select name="type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.missing_and_found_8b1afe085a') }}</option>
                        @forelse ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_report_types_5701e2167c') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.status_920e413c7d') }}
                    <select name="status" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_status_ac78229d6b') }}</option>
                        @forelse ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_statuses_b34efdc994') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.animal_3f257e684a') }}
                    <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.every_species_5c8dedc378') }}</option>
                        @forelse ($species_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_species_available_5b8f473ec2') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto]">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.city_fc33f73246') }}
                    <input name="city" value="{{ $filters['city'] ?? '' }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.vilnius_c283e0869a') }}">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.sort_bec69036aa') }}
                    <select name="sort" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($sort_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest-sighting') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_sort_options_dd1d70e52f') }}</option>
                        @endforelse
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="action action--primary action--compact">
                        <x-lucide-sliders-horizontal class="icon icon--sm" aria-hidden="true" />
                        <span>{{ __('ui.apply_31e392d1c0') }}</span>
                    </button>
                    <a href="{{ route('lost-found.index') }}" class="action action--surface action--icon" title="{{ __('ui.clear_filters_7179ea0035') }}">
                        <x-lucide-rotate-ccw class="icon icon--sm" aria-hidden="true" />
                        <span class="sr-only">{{ __('ui.clear_filters_7179ea0035') }}</span>
                    </a>
                </div>
            </div>
        </form>

        <div class="grid gap-8 xl:grid-cols-[minmax(0,1.35fr)_minmax(22rem,.65fr)]">
            <section aria-labelledby="search-results-title">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h2 id="search-results-title" class="text-2xl font-bold">{{ __('ui.search_reports_70565872e3') }}</h2>
                    <span class="text-sm text-paw-muted">{{ __('ui.newest_verified_activity_first_280c662fa5') }}</span>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    @forelse ($search_cases as $searchCase)
                        <x-search-case-card :search-case="$searchCase" />
                    @empty
                        <div class="grid min-h-60 place-items-center rounded-md border border-dashed border-paw-line bg-white p-8 text-center md:col-span-2">
                            <div>
                                <x-lucide-search-x class="mx-auto size-9 text-paw-muted" aria-hidden="true" />
                                <h3 class="mt-3 font-bold">{{ __('ui.no_matching_reports_0867fe8beb') }}</h3>
                                <p class="mt-1 text-sm text-paw-muted">{{ __('ui.try_a_wider_area_or_clear_one_of_c761fc97ac') }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-5">
                    {{ $search_cases->links() }}
                </div>
            </section>

            <aside class="xl:sticky xl:top-24 xl:self-start">
                <x-search-map :markers="$map_markers" title="{{ __('ui.visible_search_area_b5fd8ec109') }}" compact />
                <div class="mt-5 border-t border-paw-line pt-5">
                    <h2 class="font-bold">{{ __('ui.see_an_animal_d106478c0f') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-paw-muted">
                        {{ __('ui.open_the_matching_card_and_send_the_actual_5f3fd1d897') }}
                    </p>
                </div>
            </aside>
        </div>
    </div>
</x-app-shell>
