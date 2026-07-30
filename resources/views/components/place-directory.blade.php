@props(['places'])

@php
    $filters = $places['filters'];
    $queryParameters = [
        'q' => $places['query'],
        'area' => $filters['area'],
        'category' => $filters['category'],
        'species' => $filters['species'],
        'size' => $filters['size'],
        'distance' => $filters['distance'],
        'open_now' => $filters['open_now'] ? 1 : null,
        'leash' => $filters['leash'],
        'accessibility' => $filters['accessibility'],
        'safety' => $filters['safety'],
        'price' => $filters['price'],
        'rating' => $filters['rating'],
        'verification' => $filters['verification'],
        'crowd' => $filters['crowd'],
        'visit_time' => $filters['visit_time'],
        'pet' => $filters['pet'],
        'sort' => $filters['sort'],
        'view' => $filters['view'],
        'mode' => $filters['mode'],
        'layer' => $filters['layer'],
        'selected' => $places['selected']['key'] ?? null,
        'emergency' => $places['emergency'] ? 1 : null,
    ];
@endphp

<div class="place-directory">
    <section class="place-search" aria-labelledby="place-search-title">
        <div class="place-search__heading">
            <div>
                <p class="place-search__eyebrow">Search area</p>
                <h2 id="place-search-title">{{ $filters['area'] }}</h2>
            </div>
            <div class="place-search__actions">
                <x-action-control
                    :href="$places['emergency_url']"
                    label="Emergency vet"
                    icon="siren"
                    variant="danger"
                    size="compact"
                />
                <x-action-control
                    :href="$places['add_url']"
                    label="Add place"
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
                    label="Search places"
                    placeholder="Quiet evening park, bird clinic, cat groomer..."
                    :value="$places['query']"
                />

                <label class="field-group" for="place-area">
                    <span class="field-group__label">Area</span>
                    <span class="field-group__control">
                        <x-lucide-map-pin class="icon icon--sm" aria-hidden="true" />
                        <input id="place-area" name="area" type="text" value="{{ $filters['area'] }}" class="field" maxlength="120">
                    </span>
                </label>

                <label class="field-group" for="place-pet">
                    <span class="field-group__label">Plan for</span>
                    <span class="field-group__control">
                        <x-lucide-paw-print class="icon icon--sm" aria-hidden="true" />
                        <select id="place-pet" name="pet" class="field field--select">
                            <option value="scout" @selected($filters['pet'] === 'scout')>Scout</option>
                            <option value="nori" @selected($filters['pet'] === 'nori')>Nori</option>
                            <option value="none" @selected($filters['pet'] === 'none')>No pet selected</option>
                        </select>
                    </span>
                </label>

                <x-action-control type="submit" label="Search" icon="search" variant="primary" size="toolbar" />
            </div>

            @if ($places['parsed_query']['summary'])
                <p class="place-search__interpretation">
                    <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
                    {{ $places['parsed_query']['summary'] }}
                </p>
            @endif

            <div class="place-search__categories" aria-label="Place categories">
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
                    <span>No categories available.</span>
                @endforelse
            </div>

            <details class="place-search__filters" @if ($places['advanced_filters_active']) open @endif>
                <summary>
                    <x-lucide-sliders-horizontal class="icon icon--sm" aria-hidden="true" />
                    More filters
                </summary>
                <div class="place-search__filter-grid">
                    <label class="field-group" for="place-species">
                        <span class="field-group__label">Pet type</span>
                        <select id="place-species" name="species" class="field field--select">
                            @forelse ($places['species_options'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['species'] === $value)>{{ $label }}</option>
                            @empty
                                <option value="any">Any pet</option>
                            @endforelse
                        </select>
                    </label>

                    <label class="field-group" for="place-size">
                        <span class="field-group__label">Pet size</span>
                        <select id="place-size" name="size" class="field field--select">
                            @forelse ($places['size_options'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['size'] === $value)>{{ $label }}</option>
                            @empty
                                <option value="any">Any size</option>
                            @endforelse
                        </select>
                    </label>

                    @forelse ($places['filter_options'] as $name => $options)
                        <label class="field-group" for="place-{{ $name }}">
                            <span class="field-group__label">{{ \Illuminate\Support\Str::headline($name) }}</span>
                            <select id="place-{{ $name }}" name="{{ $name }}" class="field field--select">
                                @forelse ($options as $value => $label)
                                    <option value="{{ $value }}" @selected((string) $filters[$name] === (string) $value)>{{ $label }}</option>
                                @empty
                                    <option value="any">Any</option>
                                @endforelse
                            </select>
                        </label>
                    @empty
                        <p>No additional filters available.</p>
                    @endforelse

                    <label class="place-search__toggle">
                        <input type="checkbox" name="open_now" value="1" @checked($filters['open_now'])>
                        <span>
                            <x-lucide-clock-3 class="icon icon--sm" aria-hidden="true" />
                            Open now
                        </span>
                    </label>
                </div>
                <div class="place-search__filter-actions">
                    <x-action-control type="submit" label="Apply filters" icon="check" variant="primary" size="compact" />
                    <x-action-control :href="$places['browse_url']" label="Clear" icon="rotate-ccw" variant="ghost" size="compact" />
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
                <strong>{{ $places['location']['enabled'] ? 'Generalized location active' : 'Location not shared' }}</strong>
                <span>{{ $places['location']['label'] ?? 'Search still works by city, area, address, or map point.' }}</span>
            </div>
            <form method="POST" action="{{ route('actions.perform') }}" data-place-location-form>
                @csrf
                <input type="hidden" name="action" value="set-place-location">
                <input type="hidden" name="place_latitude" value="" data-place-latitude>
                <input type="hidden" name="place_longitude" value="" data-place-longitude>
                <button type="button" class="action action--surface action--compact" data-place-locate>
                    <x-lucide-locate-fixed class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $places['location']['enabled'] ? 'Refresh area' : 'Use my location' }}</span>
                </button>
                <span class="place-location__status" data-place-location-status aria-live="polite"></span>
            </form>
            @if ($places['location']['enabled'])
                <x-action-control
                    :endpoint="route('actions.perform')"
                    :payload="['action' => 'clear-place-location']"
                    label="Stop using"
                    icon="locate-off"
                    variant="ghost"
                    size="compact"
                />
            @endif
        </div>
    </section>

    <nav class="place-directory__modes" aria-label="Catalog mode">
        @forelse ($places['mode_options'] as $value => $label)
            <a
                href="{{ $places['browse_url'].'?'.http_build_query(array_filter([...$queryParameters, 'mode' => $value])) }}"
                class="{{ $filters['mode'] === $value ? 'is-active' : '' }}"
                @if ($filters['mode'] === $value) aria-current="page" @endif
            >
                {{ $label }}
            </a>
        @empty
            <span>No catalog modes available.</span>
        @endforelse
    </nav>

    <div class="place-directory__toolbar">
        <nav class="place-directory__views" aria-label="View mode">
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
                <span>No views available.</span>
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
            <label for="place-sort">Sort</label>
            <select id="place-sort" name="sort" class="field field--select" data-auto-submit>
                @forelse ($places['sort_options'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                @empty
                    <option value="recommended">Recommended</option>
                @endforelse
            </select>
            <button type="submit" class="icon-button" aria-label="Apply sorting">
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
                :emergency="$places['emergency']"
            />
        @endif

        @if ($filters['view'] !== 'map' && $filters['view'] !== 'fullscreen')
            <section class="place-results" aria-labelledby="place-results-title">
                <header class="place-results__heading">
                    <div>
                        <p>Results</p>
                        <h2 id="place-results-title">{{ count($places['items']) }} matching places</h2>
                    </div>
                    <nav class="place-results__layers" aria-label="Map layer">
                        @forelse ($places['layer_options'] as $value => $label)
                            <a
                                href="{{ $places['browse_url'].'?'.http_build_query(array_filter([...$queryParameters, 'layer' => $value])) }}"
                                @if ($filters['layer'] === $value) aria-current="page" @endif
                            >{{ $label }}</a>
                        @empty
                            <span>No layers available.</span>
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
                            title="No matching places"
                            :description="$places['empty_message']"
                        />
                    @endforelse
                </div>
            </section>
        @endif
    </div>

    @if (count($places['comparison']) > 1)
        <section class="place-comparison" aria-labelledby="place-comparison-title">
            <header>
                <p>Quick comparison</p>
                <h2 id="place-comparison-title">Top matches</h2>
            </header>
            <div class="place-comparison__table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Place</th>
                            <th scope="col">Travel</th>
                            <th scope="col">Hours</th>
                            <th scope="col">Pet rule</th>
                            <th scope="col">Access</th>
                            <th scope="col">Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($places['comparison'] as $place)
                            <tr>
                                <th scope="row"><a href="{{ $place['detail_url'] }}">{{ $place['short_name'] }}</a></th>
                                <td>{{ $place['travel_label'] }}</td>
                                <td>{{ $place['open_label'] }}</td>
                                <td>{{ $place['leash_policy'] }}</td>
                                <td>{{ $place['wheelchair_access'] ? 'Step-free listed' : 'Check access' }}</td>
                                <td>{{ $place['verification']['label'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No places available to compare.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
