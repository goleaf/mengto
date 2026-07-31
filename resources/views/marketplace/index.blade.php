<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-bold uppercase text-paw-leaf">{{ __('ui.community_marketplace_1525148f3c') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.useful_things_and_trusted_pet_services_0b2d0b997a') }}</h1>
                <p class="mt-3 max-w-2xl leading-7 text-paw-muted">
                    {{ __('ui.buy_exchange_rehome_or_book_without_exposing_your_a7174cb664') }}
                </p>
            </div>
            <x-action-control label="{{ __('ui.create_listing_815d30caa6') }}" icon="badge-plus" variant="primary" :href="route('marketplace.create')" />
        </header>

        <section class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line md:grid-cols-3 xl:grid-cols-6" aria-label="{{ __('ui.marketplace_summary_f9ecef7b29') }}">
            @forelse ([
                ['label' => __('ui.available_e674447337'), 'value' => $stats['available'], 'icon' => 'store'],
                ['label' => __('ui.for_adoption_0435a17996'), 'value' => $stats['adoption'], 'icon' => 'heart-handshake'],
                ['label' => __('ui.free_f411a1fb62'), 'value' => $stats['free'], 'icon' => 'gift'],
                ['label' => __('ui.for_rent_03cc104614'), 'value' => $stats['rental'], 'icon' => 'calendar-clock'],
                ['label' => __('ui.shelter_needs_939002282f'), 'value' => $stats['shelter'], 'icon' => 'hand-heart'],
                ['label' => __('ui.cities_95697d1449'), 'value' => $stats['cities'], 'icon' => 'map-pin'],
            ] as $stat)
                <div class="flex items-center gap-3 bg-white p-4">
                    <x-dynamic-component :component="'lucide-'.$stat['icon']" class="size-5 shrink-0 text-paw-leaf" aria-hidden="true" />
                    <div>
                        <strong class="block text-xl">{{ $stat['value'] }}</strong>
                        <span class="text-xs text-paw-muted">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">{{ __('ui.marketplace_statistics_are_unavailable_c530fed378') }}</p>
            @endforelse
        </section>

        <form method="GET" action="{{ route('marketplace.index') }}" class="grid gap-4 border-y border-paw-line py-5" role="search">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,2fr)_repeat(3,minmax(10rem,1fr))]">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.search_49c266baaa') }}
                    <span class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3">
                        <x-lucide-search class="size-4 text-paw-muted" aria-hidden="true" />
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 outline-none" placeholder="{{ __('ui.item_service_city_or_category_2e46b38259') }}">
                    </span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.listing_type_329627e862') }}
                    <select name="type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.all_types_f10988e79e') }}</option>
                        @forelse ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_listing_types_1b2c3d3c8d') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.category_292c06f004') }}
                    <select name="category" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.all_categories_9d5097a837') }}</option>
                        @forelse ($categories as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_categories_29b8c8b535') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.pet_8f0d1b30eb') }}
                    <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.every_pet_type_b4aed4a4ff') }}</option>
                        @forelse ($species_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_pet_types_e1150f17ef') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.city_fc33f73246') }}
                    <input name="city" value="{{ $filters['city'] ?? '' }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.vilnius_c283e0869a') }}">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.handover_c012b47252') }}
                    <select name="delivery" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_option_2fc1501a67') }}</option>
                        @forelse ($delivery_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['delivery'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_handover_options_a7e9700b2b') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.price_93c91c851e') }}
                    <select name="price" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($price_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['price'] ?? 'any') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_price_filters_764fac2c44') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.condition_39b36d38d6') }}
                    <select name="condition" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_condition_8d8c95487f') }}</option>
                        @forelse ($conditions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['condition'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_condition_filters_e8ebcb68ab') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.seller_01498fa31d') }}
                    <select name="seller_type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_seller_d512c305bd') }}</option>
                        @forelse ($seller_types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['seller_type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_seller_filters_a0963d00ee') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.availability_12f67f8539') }}
                    <select name="availability" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_availability_372ccd0787') }}</option>
                        @forelse ($availability_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['availability'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_availability_filters_fa18f0256c') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.sort_bec69036aa') }}
                    <select name="sort" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($sort_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_sort_options_dd1d70e52f') }}</option>
                        @endforelse
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="action action--primary action--compact flex-1">
                        <x-lucide-sliders-horizontal class="icon icon--sm" aria-hidden="true" />
                        <span>{{ __('ui.apply_31e392d1c0') }}</span>
                    </button>
                    <a href="{{ route('marketplace.index') }}" class="action action--surface action--icon" title="{{ __('ui.clear_filters_7179ea0035') }}">
                        <x-lucide-rotate-ccw class="icon icon--sm" aria-hidden="true" />
                        <span class="sr-only">{{ __('ui.clear_filters_7179ea0035') }}</span>
                    </a>
                </div>
            </div>
        </form>

        <section aria-labelledby="listings-heading">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="listings-heading" class="text-xl font-bold">{{ __('ui.available_now_2a4729fa76') }}</h2>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('ui.community_status_is_not_a_guarantee_inspect_items_4a9153ad08') }}</p>
                </div>
                <span class="inline-flex items-center gap-2 text-sm font-semibold text-paw-muted">
                    <x-lucide-shield-check class="size-4 text-paw-leaf" aria-hidden="true" />
                    {{ __('ui.platform_only_contact_51f3af5138') }}
                </span>
            </div>

            <div class="market-grid">
                @forelse ($listings as $listing)
                    <x-listing-card :listing="$listing" />
                @empty
                    <div class="market-empty">
                        <x-lucide-search-x class="size-8 text-paw-muted" aria-hidden="true" />
                        <h3 class="mt-3 text-xl font-bold">{{ __('ui.no_exact_match_yet_85432de381') }}</h3>
                        <p class="mt-2 max-w-xl text-paw-muted">{{ __('ui.remove_one_filter_search_a_nearby_city_or_528eb60b39') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $listings->links() }}</div>
        </section>
    </div>
</x-app-shell>
