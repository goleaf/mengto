<div class="place-directory">
    <section class="place-search" aria-labelledby="place-search-title">
        <div class="place-search__heading">
            <div>
                <p class="place-search__eyebrow">{{ __('ui.search_area_20653cdb60') }}</p>
                <h2 id="place-search-title">{{ $filters['area'] }}</h2>
            </div>
            <div class="place-search__actions">
                <x-action-control
                    :href="$places['emergency_url']"
                    label="{{ __('ui.emergency_vet_17cb260588') }}"
                    icon="siren"
                    variant="danger"
                    size="compact"
                />
                <x-action-control
                    :href="$places['add_url']"
                    label="{{ __('ui.add_place_b37bea1398') }}"
                    icon="map-pin-plus"
                    variant="surface"
                    size="compact"
                />
            </div>
        </div>

        <form method="GET" action="{{ $places['browse_url'] }}" class="place-search__form" data-place-search-form>
            <div class="place-search__primary">
                <x-search-field
                    id="place-search"
                    label="{{ __('ui.search_places_5b8a429653') }}"
                    placeholder="{{ __('ui.quiet_evening_park_bird_clinic_cat_groomer_7ffbb308aa') }}"
                    :value="$places['query']"
                />

                <label class="field-group" for="place-area">
                    <span class="field-group__label">{{ __('ui.area_024dc204d7') }}</span>
                    <span class="field-group__control">
                        <x-lucide-map-pin class="icon icon--sm" aria-hidden="true" />
                        <input id="place-area" name="area" type="text" value="{{ $filters['area'] }}" class="field" maxlength="120">
                    </span>
                </label>

                <label class="field-group" for="place-pet">
                    <span class="field-group__label">{{ __('ui.plan_for_0095cfdeee') }}</span>
                    <span class="field-group__control">
                        <x-lucide-paw-print class="icon icon--sm" aria-hidden="true" />
                        <select id="place-pet" name="pet" class="field field--select">
                            <option value="scout" @selected($filters['pet'] === 'scout')>{{ __('ui.scout_8a1db462be') }}</option>
                            <option value="nori" @selected($filters['pet'] === 'nori')>{{ __('ui.nori_a64203ba20') }}</option>
                            <option value="none" @selected($filters['pet'] === 'none')>{{ __('ui.no_pet_selected_8fb952f34e') }}</option>
                        </select>
                    </span>
                </label>

                <x-action-control type="submit" label="{{ __('ui.search_49c266baaa') }}" icon="search" variant="primary" size="toolbar" />
            </div>

            @if ($places['parsed_query']['summary'])
                <p class="place-search__interpretation">
                    <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
                    {{ $places['parsed_query']['summary'] }}
                </p>
            @endif

            <div class="place-search__categories" aria-label="{{ __('ui.place_categories_021cf6fc23') }}">
                @forelse ($places['category_options'] as $value => $label)
                    <button
                        type="submit"
                        name="category"
                        value="{{ $value }}"
                        class="place-filter-chip {{ $filters['category'] === $value ? 'place-filter-chip--active' : '' }}"
                        aria-pressed="{{ $filters['category'] === $value ? 'true' : 'false' }}"
                    >
                        <x-dynamic-component
                            :component="'lucide-'.($value === 'all' ? 'layout-grid' : match ($value) {
                                'park' => 'trees',
                                'dog-park' => 'fence',
                                'route' => 'route',
                                'vet' => 'stethoscope',
                                'emergency-vet' => 'siren',
                                'pet-store' => 'shopping-bag',
                                'grooming' => 'scissors',
                                'shelter' => 'house-heart',
                                'pet-cafe' => 'coffee',
                            })"
                            class="icon icon--sm"
                            aria-hidden="true"
                        />
                        <span>{{ $label }}</span>
                    </button>
                @empty
                    <span>{{ __('ui.no_categories_available_a557500f61') }}</span>
                @endforelse
            </div>

            <details class="place-search__filters" @if ($places['advanced_filters_active']) open @endif>
                <summary>
                    <x-lucide-sliders-horizontal class="icon icon--sm" aria-hidden="true" />
                    {{ __('ui.more_filters_b1ab49a6fe') }}
                </summary>
                <div class="place-search__filter-grid">
                    <label class="field-group" for="place-species">
                        <span class="field-group__label">{{ __('ui.pet_type_5e43b05408') }}</span>
                        <select id="place-species" name="species" class="field field--select">
                            @forelse ($places['species_options'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['species'] === $value)>{{ $label }}</option>
                            @empty
                                <option value="any">{{ __('ui.any_pet_206a508d72') }}</option>
                            @endforelse
                        </select>
                    </label>

                    <label class="field-group" for="place-size">
                        <span class="field-group__label">{{ __('ui.pet_size_8458af913c') }}</span>
                        <select id="place-size" name="size" class="field field--select">
                            @forelse ($places['size_options'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['size'] === $value)>{{ $label }}</option>
                            @empty
                                <option value="any">{{ __('ui.any_size_9f46b4f2f6') }}</option>
                            @endforelse
                        </select>
                    </label>

                    @forelse ($places['filter_options'] as $name => $options)
                        <label class="field-group" for="place-{{ $name }}">
                            <span class="field-group__label">{{ $places['filter_labels'][$name] }}</span>
                            <select id="place-{{ $name }}" name="{{ $name }}" class="field field--select">
                                @forelse ($options as $value => $label)
                                    <option value="{{ $value }}" @selected((string) $filters[$name] === (string) $value)>{{ $label }}</option>
                                @empty
                                    <option value="any">{{ __('ui.any_2b505597da') }}</option>
                                @endforelse
                            </select>
                        </label>
                    @empty
                        <p>{{ __('ui.no_additional_filters_available_2ffad42832') }}</p>
                    @endforelse

                    <label class="place-search__toggle">
                        <input type="checkbox" name="open_now" value="1" @checked($filters['open_now'])>
                        <span>
                            <x-lucide-clock-3 class="icon icon--sm" aria-hidden="true" />
                            {{ __('ui.open_now_14b67e6207') }}
                        </span>
                    </label>
                </div>
                <div class="place-search__filter-actions">
                    <x-action-control type="submit" label="{{ __('ui.apply_filters_d80ab19b7e') }}" icon="check" variant="primary" size="compact" />
                    <x-action-control :href="$places['browse_url']" label="{{ __('ui.clear_83b12c2216') }}" icon="rotate-ccw" variant="ghost" size="compact" />
                </div>
            </details>

            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            <input type="hidden" name="view" value="{{ $filters['view'] }}">
            <input type="hidden" name="mode" value="{{ $filters['mode'] }}">
            <input type="hidden" name="layer" value="{{ $filters['layer'] }}">
            @if ($places['emergency'])
                <input type="hidden" name="emergency" value="1">
            @endif
        </form>

        <div class="place-location">
            <div>
                <strong>{{ $places['location']['enabled'] ? __('ui.generalized_location_active_2f7a7a697d') : __('ui.location_not_shared_4c6e250730') }}</strong>
                <span>{{ $places['location']['label'] ?? __('ui.search_still_works_by_city_area_address_or_7622a02e17') }}</span>
            </div>
            <form
                method="POST"
                action="{{ route('actions.perform') }}"
                data-place-location-form
                data-location-unavailable="{{ __('ui.location_unavailable_browser_f706e68bb7') }}"
                data-location-loading="{{ __('ui.location_approximate_loading_68d9fb18f7') }}"
                data-location-received="{{ __('ui.location_approximate_received_2762de576f') }}"
                data-location-denied="{{ __('ui.location_permission_denied_38657a54fb') }}"
            >
                @csrf
                <input type="hidden" name="action" value="set-place-location">
                <input type="hidden" name="place_latitude" value="" data-place-latitude>
                <input type="hidden" name="place_longitude" value="" data-place-longitude>
                <button type="button" class="action action--surface action--compact" data-place-locate>
                    <x-lucide-locate-fixed class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $places['location']['enabled'] ? __('ui.refresh_area_e3dd46c502') : __('ui.use_my_location_30cbc33ba1') }}</span>
                </button>
                <span class="place-location__status" data-place-location-status aria-live="polite"></span>
            </form>
            @if ($places['location']['enabled'])
                <x-action-control
                    :endpoint="route('actions.perform')"
                    :payload="['action' => 'clear-place-location']"
                    label="{{ __('ui.stop_using_ef22ba68c9') }}"
                    icon="locate-off"
                    variant="ghost"
                    size="compact"
                />
            @endif
        </div>
    </section>

    <nav class="place-directory__modes" aria-label="{{ __('ui.catalog_mode_0697521ad1') }}">
        @forelse ($places['mode_options'] as $value => $label)
            <a
                href="{{ $places['browse_url'].'?'.http_build_query(array_filter([...$queryParameters, 'mode' => $value])) }}"
                class="{{ $filters['mode'] === $value ? 'is-active' : '' }}"
                @if ($filters['mode'] === $value) aria-current="page" @endif
            >
                {{ $label }}
            </a>
        @empty
            <span>{{ __('ui.no_catalog_modes_available_515199bbbc') }}</span>
        @endforelse
    </nav>

    <div class="place-directory__toolbar">
        <nav class="place-directory__views" aria-label="{{ __('ui.view_mode_18997f2413') }}">
            @forelse ($places['view_options'] as $value => $label)
                <a
                    href="{{ $places['browse_url'].'?'.http_build_query(array_filter([...$queryParameters, 'view' => $value])) }}"
                    class="{{ $filters['view'] === $value ? 'is-active' : '' }}"
                    @if ($filters['view'] === $value) aria-current="page" @endif
                    title="{{ $label }}"
                >
                    <x-dynamic-component
                        :component="'lucide-'.match ($value) {
                            'map' => 'map',
                            'list' => 'list',
                            'fullscreen' => 'maximize-2',
                            'route' => 'route',
                            default => 'panel-left',
                        }"
                        class="icon icon--sm"
                        aria-hidden="true"
                    />
                    <span class="sr-only">{{ $label }}</span>
                </a>
            @empty
                <span>{{ __('ui.no_views_available_1e4b6ebfe8') }}</span>
            @endforelse
        </nav>

        <form method="GET" action="{{ $places['browse_url'] }}" class="place-directory__sort">
            @forelse ($queryParameters as $name => $value)
                @if (! in_array($name, ['sort', 'selected'], true) && $value !== null && $value !== '')
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
            @empty
                <input type="hidden" name="area" value="{{ $filters['area'] }}">
            @endforelse
            <label for="place-sort">{{ __('ui.sort_bec69036aa') }}</label>
            <select id="place-sort" name="sort" class="field field--select" data-auto-submit>
                @forelse ($places['sort_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                @empty
                    <option value="recommended">{{ __('ui.recommended_d70604e843') }}</option>
                @endforelse
            </select>
            <button type="submit" class="icon-button" aria-label="{{ __('ui.apply_sorting_323ef154f9') }}">
                <x-lucide-arrow-up-down class="icon icon--sm" aria-hidden="true" />
            </button>
        </form>
    </div>

    <div class="place-directory__workspace place-directory__workspace--{{ $filters['view'] }}">
        @if ($filters['view'] !== 'list')
            <x-place-map
                :places="$places['map_items']"
                :selected="$places['selected']"
                :layer="$filters['layer']"
                :layer-label="$places['layer_options'][$filters['layer']]"
                :emergency="$places['emergency']"
            />
        @endif

        @if ($filters['view'] !== 'map' && $filters['view'] !== 'fullscreen')
            <section class="place-results" aria-labelledby="place-results-title">
                <header class="place-results__heading">
                    <div>
                        <p>{{ __('ui.results_219c4a6c86') }}</p>
                        <h2 id="place-results-title">{{ trans_choice('presentation.matching_places', count($places['items']), ['count' => count($places['items'])]) }}</h2>
                    </div>
                    <nav class="place-results__layers" aria-label="{{ __('ui.map_layer_d820abaaf8') }}">
                        @forelse ($places['layer_options'] as $value => $label)
                            <a
                                href="{{ $places['browse_url'].'?'.http_build_query(array_filter([...$queryParameters, 'layer' => $value])) }}"
                                @if ($filters['layer'] === $value) aria-current="page" @endif
                            >{{ $label }}</a>
                        @empty
                            <span>{{ __('ui.no_layers_available_369d1c6483') }}</span>
                        @endforelse
                    </nav>
                </header>

                <div class="place-results__list">
                    @forelse ($places['items'] as $place)
                        <x-place-card
                            :place="$place"
                            :selected="($places['selected']['key'] ?? null) === $place['key']"
                        />
                    @empty
                        <x-empty-state
                            icon="map-pin-off"
                            title="{{ __('ui.no_matching_places_10ec44f4ec') }}"
                            :description="$places['empty_message']"
                        />
                    @endforelse
                </div>

                @if ($places['pagination']['last_page'] > 1)
                    <nav
                        class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4"
                        aria-label="{{ __('places.pagination.label') }}"
                        data-place-pagination
                    >
                        <p class="text-sm text-slate-600">{{ $places['pagination']['summary'] }}</p>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($places['pagination']['previous_url'])
                                <x-action-control
                                    :href="$places['pagination']['previous_url']"
                                    label="{{ __('places.pagination.previous') }}"
                                    icon="chevron-left"
                                    variant="surface"
                                    size="compact"
                                />
                            @endif

                            @forelse ($places['pagination']['pages'] as $page)
                                <a
                                    href="{{ $page['url'] }}"
                                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border px-3 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700 {{ $page['current'] ? 'border-sky-700 bg-sky-700 text-white' : 'border-slate-300 bg-white text-slate-800 hover:border-sky-600' }}"
                                    aria-label="{{ $page['label'] }}"
                                    @if ($page['current']) aria-current="page" @endif
                                >
                                    {{ $loop->iteration }}
                                </a>
                            @empty
                                <span>{{ $places['pagination']['summary'] }}</span>
                            @endforelse

                            @if ($places['pagination']['next_url'])
                                <x-action-control
                                    :href="$places['pagination']['next_url']"
                                    label="{{ __('places.pagination.next') }}"
                                    icon="chevron-right"
                                    variant="surface"
                                    size="compact"
                                />
                            @endif
                        </div>
                    </nav>
                @endif
            </section>
        @endif
    </div>

    @if (count($places['comparison']) > 1)
        <section class="place-comparison" aria-labelledby="place-comparison-title">
            <header>
                <p>{{ __('ui.quick_comparison_e1d6250e16') }}</p>
                <h2 id="place-comparison-title">{{ __('ui.top_matches_ca85a90f5c') }}</h2>
            </header>
            <div class="place-comparison__table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">{{ __('ui.place_e9463dccf0') }}</th>
                            <th scope="col">{{ __('ui.travel_d2b98fb537') }}</th>
                            <th scope="col">{{ __('ui.hours_21e8492938') }}</th>
                            <th scope="col">{{ __('ui.pet_rule_05863e283c') }}</th>
                            <th scope="col">{{ __('ui.access_ec5ba0abb7') }}</th>
                            <th scope="col">{{ __('ui.source_0e570ca6fa') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($places['comparison'] as $place)
                            <tr>
                                <th scope="row"><a href="{{ $place['detail_url'] }}">{{ $place['short_name'] }}</a></th>
                                <td>{{ $place['travel_label'] }}</td>
                                <td>{{ $place['open_label'] }}</td>
                                <td>{{ $place['leash_policy'] }}</td>
                                <td>{{ $place['wheelchair_access'] ? __('ui.step_free_listed_7131755ea6') : __('ui.check_access_abb9a172a6') }}</td>
                                <td>{{ $place['verification']['label'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">{{ __('ui.no_places_available_to_compare_ab63a16ec8') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
