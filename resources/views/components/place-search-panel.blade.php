@props([
    'places',
    'filters',
])

<section class="place-search" aria-labelledby="place-search-title">
    <div class="place-search__heading">
        <div>
            <p class="place-search__eyebrow">{{ __('ui.search_area_20653cdb60') }}</p>
            <h2 id="place-search-title">{{ $filters['area'] }}</h2>
        </div>
        <x-action-control
            :href="$places['emergency_url']"
            label="{{ __('ui.emergency_vet_17cb260588') }}"
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
                label="{{ __('ui.search_places_5b8a429653') }}"
                placeholder="{{ __('ui.quiet_evening_park_bird_clinic_cat_groomer_7ffbb308aa') }}"
                :value="$places['query']"
            />

            <label class="field-group" for="place-area">
                <span class="field-group__label">{{ __('ui.area_024dc204d7') }}</span>
                <span class="field-group__control">
                    <x-ui-icon name="map-pin" size="sm" />
                    <input id="place-area" name="area" type="text" value="{{ $filters['area'] }}" class="field" maxlength="120">
                </span>
            </label>

            <label class="field-group" for="place-pet">
                <span class="field-group__label">{{ __('ui.plan_for_0095cfdeee') }}</span>
                <span class="field-group__control">
                    <x-ui-icon name="paw-print" size="sm" />
                    <select id="place-pet" name="pet" class="field field--select">
                        <option value="scout" @selected($filters['pet'] === 'scout')>{{ __('ui.scout_8a1db462be') }}</option>
                        <option value="nori" @selected($filters['pet'] === 'nori')>{{ __('ui.nori_a64203ba20') }}</option>
                        <option value="none" @selected($filters['pet'] === 'none')>{{ __('ui.no_pet_selected_8fb952f34e') }}</option>
                    </select>
                </span>
            </label>

            <x-action-control
                class="place-search__submit"
                type="submit"
                label="{{ __('ui.search_49c266baaa') }}"
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

        <div class="place-search__categories" aria-label="{{ __('ui.place_categories_021cf6fc23') }}">
            @forelse ($places['category_options'] as $value => $label)
                <button
                    type="submit"
                    name="category"
                    value="{{ $value }}"
                    class="place-filter-chip {{ $filters['category'] === $value ? 'place-filter-chip--active' : '' }}"
                    aria-pressed="{{ $filters['category'] === $value ? 'true' : 'false' }}"
                >
                    <x-ui-icon size="sm" :name="($value === 'all' ? 'layout-grid' : match ($value) { 'park' => 'trees', 'dog-park' => 'fence', 'route' => 'route', 'vet' => 'stethoscope', 'emergency-vet' => 'siren', 'pet-store' => 'shopping-bag', 'grooming' => 'scissors', 'shelter' => 'house-heart', 'pet-cafe' => 'coffee', })" />
                    <span>{{ $label }}</span>
                </button>
            @empty
                <span>{{ __('ui.no_categories_available_a557500f61') }}</span>
            @endforelse
        </div>

        <details class="place-search__filters" @if ($places['advanced_filters_active']) open @endif>
            <summary>
                <x-ui-icon name="sliders-horizontal" size="sm" />
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
                        <x-ui-icon name="clock-3" size="sm" />
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
        <div class="place-location__copy">
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
                <x-ui-icon name="locate-fixed" size="sm" />
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
