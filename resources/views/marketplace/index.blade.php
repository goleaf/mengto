<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <x-page-header
            :eyebrow="__('ui.community_marketplace')"
            :title="__('ui.useful_things_and_trusted_pet_services')"
            :description="__('ui.buy_exchange_rehome_or_book_without_exposing_your_phone_number_or_home_address_before_both_sides_agree')"
            heading-id="marketplace-heading"
            :action-label="__('ui.create_listing')"
            action-icon="badge-plus"
            :action-href="route('marketplace.create')"
            data-section="marketplace-header"
        />

        <section data-marketplace-stats class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line md:grid-cols-3 xl:grid-cols-6" aria-label="{{ __('ui.marketplace_summary') }}">
            @forelse ([
                ['label' => __('ui.available'), 'value' => $stats['available'], 'icon' => 'store'],
                ['label' => __('ui.for_adoption'), 'value' => $stats['adoption'], 'icon' => 'heart-handshake'],
                ['label' => __('ui.free'), 'value' => $stats['free'], 'icon' => 'gift'],
                ['label' => __('ui.for_rent'), 'value' => $stats['rental'], 'icon' => 'calendar-clock'],
                ['label' => __('ui.shelter_needs'), 'value' => $stats['shelter'], 'icon' => 'hand-heart'],
                ['label' => __('ui.cities'), 'value' => $stats['cities'], 'icon' => 'map-pin'],
            ] as $stat)
                <div data-marketplace-stat class="flex items-center gap-3 bg-white p-4">
                    <x-ui-icon size="lg" :name="$stat['icon']" class="shrink-0 text-paw-leaf" />
                    <div>
                        <strong class="block text-xl">{{ $stat['value'] }}</strong>
                        <span class="text-xs text-paw-muted">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">{{ __('ui.marketplace_statistics_are_unavailable') }}</p>
            @endforelse
        </section>

        <form data-marketplace-filters method="GET" action="{{ route('marketplace.index') }}" class="grid gap-4 border-y border-paw-line py-5" role="search">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,2fr)_repeat(3,minmax(10rem,1fr))]">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.search') }}
                    <span class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3">
                        <x-ui-icon name="search" size="sm" class="text-paw-muted" />
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 outline-none" placeholder="{{ __('ui.item_service_city_or_category') }}">
                    </span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.listing_type') }}
                    <select name="type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.all_types') }}</option>
                        @forelse ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_listing_types') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.category') }}
                    <select name="category" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.all_categories') }}</option>
                        @forelse ($categories as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_categories') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.pet') }}
                    <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.every_pet_type') }}</option>
                        @forelse ($species_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_pet_types') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.city') }}
                    <input name="city" value="{{ $filters['city'] ?? '' }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.vilnius') }}">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.handover') }}
                    <select name="delivery" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_option') }}</option>
                        @forelse ($delivery_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['delivery'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_handover_options') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.price') }}
                    <select name="price" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($price_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['price'] ?? 'any') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_price_filters') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.condition') }}
                    <select name="condition" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_condition') }}</option>
                        @forelse ($conditions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['condition'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_condition_filters') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.seller') }}
                    <select name="seller_type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_seller') }}</option>
                        @forelse ($seller_types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['seller_type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_seller_filters') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.availability') }}
                    <select name="availability" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_availability') }}</option>
                        @forelse ($availability_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['availability'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_availability_filters') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.sort') }}
                    <select name="sort" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($sort_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_sort_options') }}</option>
                        @endforelse
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="action action--primary action--compact flex-1">
                        <x-ui-icon name="sliders-horizontal" size="sm" />
                        <span>{{ __('ui.apply') }}</span>
                    </button>
                    <a href="{{ route('marketplace.index') }}" class="action action--surface action--icon" title="{{ __('ui.clear_filters') }}">
                        <x-ui-icon name="rotate-ccw" size="sm" />
                        <span class="sr-only">{{ __('ui.clear_filters') }}</span>
                    </a>
                </div>
            </div>
        </form>

        <section aria-labelledby="listings-heading">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 data-marketplace-results-title id="listings-heading" class="text-xl font-bold">{{ __('ui.available_now') }}</h2>
                    <p data-marketplace-results-description class="mt-1 text-sm text-paw-muted">{{ __('ui.community_status_is_not_a_guarantee_inspect_items_and_verify_services_before_payment') }}</p>
                </div>
                <span data-marketplace-results-privacy class="inline-flex items-center gap-2 text-sm font-semibold text-paw-muted">
                    <x-ui-icon name="shield-check" size="sm" class="text-paw-leaf" />
                    {{ __('ui.platform_only_contact') }}
                </span>
            </div>

            <div class="market-grid">
                @forelse ($listings as $listing)
                    <x-listing-card :listing="$listing" />
                @empty
                    <div class="market-empty">
                        <x-ui-icon name="search-x" size="2xl" class="text-paw-muted" />
                        <h3 class="mt-3 text-xl font-bold">{{ __('ui.no_exact_match_yet') }}</h3>
                        <p class="mt-2 max-w-xl text-paw-muted">{{ __('ui.remove_one_filter_search_a_nearby_city_or_create_a_clear_request_in_the_forum') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $listings->links() }}</div>
        </section>
    </div>
</x-app-shell>
