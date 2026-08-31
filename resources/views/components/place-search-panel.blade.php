@props([
    'places',
    'filters',
])

<section class="place-search" aria-labelledby="place-search-title">
    <div class="place-search__heading">
        <div>
            <p class="place-search__eyebrow">{{ __('place_directory.search.eyebrow') }}</p>
            <h2 id="place-search-title">{{ $filters['area'] }}</h2>
        </div>
        <x-action-control
            :href="$places['emergency_url']"
            label="{{ __('place_directory.search.emergency_action') }}"
            icon="siren"
            variant="danger"
            size="compact"
        />
    </div>

    <form method="GET" action="{{ $places['browse_url'] }}" class="place-search__form" data-place-search-form>
        <div class="place-search__primary">
            <x-search-field
                id="place-search"
                class="place-search__query"
                label="{{ __('place_directory.search.label') }}"
                placeholder="{{ __('place_directory.search.placeholder') }}"
                :value="$places['query']"
            />

            <label class="field-group" for="place-area">
                <span class="field-group__label">{{ __('place_directory.search.area') }}</span>
                <span class="field-group__control">
                    <x-ui-icon name="map-pin" size="sm" />
                    <input id="place-area" name="area" type="text" value="{{ $filters['area'] }}" class="field" maxlength="120">
                </span>
            </label>

            <label class="field-group" for="place-pet">
                <span class="field-group__label">{{ __('place_directory.search.plan_for') }}</span>
                <span class="field-group__control">
                    <x-ui-icon name="paw-print" size="sm" />
                    <select id="place-pet" name="pet" class="field field--select">
                        @foreach ($places['pet_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['pet'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </span>
            </label>

            <x-action-control
                class="place-search__submit"
                type="submit"
                label="{{ __('place_directory.search.submit') }}"
                icon="search"
                variant="primary"
                size="toolbar"
            />
        </div>

        @if ($places['parsed_query']['summary'])
            <p class="place-search__interpretation">
                <x-ui-icon name="sparkles" size="sm" />
                {{ $places['parsed_query']['summary'] }}
            </p>
        @endif

        <div class="place-search__categories" aria-label="{{ __('place_directory.search.categories_label') }}" data-place-categories>
            @forelse ($places['category_options'] as $value => $label)
                <button
                    type="submit"
                    name="category"
                    value="{{ $value }}"
                    class="place-filter-chip {{ $filters['category'] === $value ? 'place-filter-chip--active' : '' }}"
                    aria-pressed="{{ $filters['category'] === $value ? 'true' : 'false' }}"
                >
                    <x-ui-icon size="sm" :name="$places['category_icons'][$value]" />
                    <span>{{ $label }}</span>
                </button>
            @empty
                <span>{{ __('place_directory.search.no_categories') }}</span>
            @endforelse
        </div>

        <details class="place-search__filters" @if ($places['advanced_filters_active']) open @endif>
            <summary>
                <x-ui-icon name="sliders-horizontal" size="sm" />
                {{ __('place_directory.search.more_filters') }}
            </summary>
            <div class="place-search__filter-grid">
                <label class="field-group" for="place-species">
                    <span class="field-group__label">{{ __('place_directory.search.pet_type') }}</span>
                    <select id="place-species" name="species" class="field field--select">
                        @forelse ($places['species_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['species'] === $value)>{{ $label }}</option>
                        @empty
                            <option value="any">{{ __('place_directory.options.species.any') }}</option>
                        @endforelse
                    </select>
                </label>

                <label class="field-group" for="place-size">
                    <span class="field-group__label">{{ __('place_directory.search.pet_size') }}</span>
                    <select id="place-size" name="size" class="field field--select">
                        @forelse ($places['size_options'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['size'] === $value)>{{ $label }}</option>
                        @empty
                            <option value="any">{{ __('place_directory.options.sizes.any') }}</option>
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
                                <option value="any">{{ __('place_directory.search.any') }}</option>
                            @endforelse
                        </select>
                    </label>
                @empty
                    <p>{{ __('place_directory.search.no_additional_filters') }}</p>
                @endforelse

                <label class="place-search__toggle">
                    <input type="checkbox" name="open_now" value="1" @checked($filters['open_now'])>
                    <span>
                        <x-ui-icon name="clock-3" size="sm" />
                        {{ __('place_directory.search.open_now') }}
                    </span>
                </label>
            </div>
            <div class="place-search__filter-actions">
                <x-action-control type="submit" label="{{ __('place_directory.search.apply') }}" icon="check" variant="primary" size="compact" />
                <x-action-control :href="$places['browse_url']" label="{{ __('place_directory.search.clear') }}" icon="rotate-ccw" variant="ghost" size="compact" />
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
        <div class="place-location__copy">
            <strong>{{ $places['location']['enabled'] ? __('place_directory.search.location.active') : __('place_directory.search.location.inactive') }}</strong>
            <span>{{ $places['location']['label'] ?? __('place_directory.search.location.manual_search') }}</span>
        </div>
        <form
            method="POST"
            action="{{ route('actions.perform') }}"
            data-place-location-form
            data-location-unavailable="{{ __('place_directory.search.location.unavailable') }}"
            data-location-loading="{{ __('place_directory.search.location.loading') }}"
            data-location-received="{{ __('place_directory.search.location.received') }}"
            data-location-denied="{{ __('place_directory.search.location.denied') }}"
        >
            @csrf
            <input type="hidden" name="action" value="set-place-location">
            <input type="hidden" name="place_latitude" value="" data-place-latitude>
            <input type="hidden" name="place_longitude" value="" data-place-longitude>
            <button type="button" class="action action--surface action--compact" data-place-locate>
                <x-ui-icon name="locate-fixed" size="sm" />
                <span>{{ $places['location']['enabled'] ? __('place_directory.search.location.refresh') : __('place_directory.search.location.use') }}</span>
            </button>
            <span class="place-location__status" data-place-location-status aria-live="polite"></span>
        </form>
        @if ($places['location']['enabled'])
            <x-action-control
                :endpoint="route('actions.perform')"
                :payload="['action' => 'clear-place-location']"
                label="{{ __('place_directory.search.location.stop') }}"
                icon="locate-off"
                variant="ghost"
                size="compact"
            />
        @endif
    </div>
</section>
