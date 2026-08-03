@props(['listing'])

<article data-listing-card class="market-card">
    <a href="{{ route('marketplace.show', $listing['slug']) }}" class="market-card__media" aria-label="{{ __('presentation.view_listing', ['title' => $listing['title']]) }}">
        @if ($listing['cover_url'])
            <img src="{{ $listing['cover_url'] }}" alt="{{ $listing['title'] }}" loading="lazy">
        @else
            <span class="market-card__placeholder" aria-hidden="true">
                <x-ui-icon size="3xl" :name="$listing['type_icon']" />
            </span>
        @endif
        <span data-listing-type class="market-card__type">
            <x-ui-icon size="sm" :name="$listing['type_icon']" />
            {{ $listing['type_label'] }}
        </span>
    </a>

    <div class="market-card__body">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p data-listing-category class="text-xs font-bold uppercase text-paw-leaf">{{ $listing['category_label'] }}</p>
                <x-card-heading
                    :title="$listing['title']"
                    :href="route('marketplace.show', $listing['slug'])"
                    spacing="compact"
                />
            </div>
            <strong class="shrink-0 text-lg">{{ $listing['price_label'] }}</strong>
        </div>

        @if ($listing['brand_model'])
            <p class="text-sm font-semibold text-paw-muted">{{ $listing['brand_model'] }}</p>
        @endif

        <x-card-description spacing="none" class="min-h-12">{{ $listing['excerpt'] }}</x-card-description>

        <div data-listing-species class="flex flex-wrap gap-2" aria-label="{{ __('ui.suitable_pets_f64e6eef51') }}">
            @forelse (array_slice($listing['species_labels'], 0, 3) as $species)
                <span class="tag">{{ $species }}</span>
            @empty
                <span class="text-xs text-paw-muted">{{ __('ui.pet_type_not_specified_af58a4e2cc') }}</span>
            @endforelse
        </div>

        <dl class="market-card__facts">
            <div>
                <dt data-listing-location-label><x-ui-icon name="map-pin" size="sm" /> {{ __('ui.location_15b61974b2') }}</dt>
                <dd>{{ $listing['location_label'] }}</dd>
            </div>
            <div>
                <dt data-listing-availability-label><x-ui-icon name="package-check" size="sm" /> {{ __('ui.availability_12f67f8539') }}</dt>
                <dd>{{ $listing['availability_label'] }} · {{ $listing['quantity'] }}</dd>
            </div>
        </dl>

        <footer class="market-card__footer">
            <div class="min-w-0 flex-1 basis-40 text-xs text-paw-muted">
                <span class="flex items-center gap-1 truncate font-semibold text-paw-ink">
                    {{ $listing['business_name'] ?? $listing['owner_name'] }}
                    @if ($listing['seller_verified'])
                        <x-ui-icon name="badge-check" size="sm" class="shrink-0 text-paw-leaf" label="{{ __('ui.verified_seller_8988c729d5') }}" />
                    @endif
                </span>
                <span data-listing-seller-type>
                    {{ $listing['seller_type_label'] }}
                    @if ($listing['item_rating'])
                        · {{ $listing['item_rating'] }}/5 ({{ $listing['reviews_count'] }})
                    @endif
                </span>
            </div>
            <x-card-action-row fill class="flex-1 basis-48 justify-end">
                <x-action-control
                    data-listing-save
                    :label="$listing['is_saved'] ? __('ui.saved_b5c120b316') : __('ui.save_1509f561f2')"
                    :active-label="$listing['is_saved'] ? __('ui.saved_b5c120b316') : null"
                    icon="bookmark"
                    active-icon="bookmark-check"
                    :active="$listing['is_saved']"
                    :pressed="$listing['is_saved']"
                    :endpoint="route('marketplace.actions', $listing['slug'])"
                    :payload="['action' => 'toggle-save']"
                />
                <x-action-control data-listing-view label="{{ __('ui.view_dcc839a401') }}" icon="arrow-right" variant="primary" :href="route('marketplace.show', $listing['slug'])" />
            </x-card-action-row>
        </footer>
    </div>
</article>
