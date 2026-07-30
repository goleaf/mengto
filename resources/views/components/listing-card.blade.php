@props(['listing'])

<article class="market-card">
    <a href="{{ route('marketplace.show', $listing['slug']) }}" class="market-card__media" aria-label="{{ __('presentation.view_listing', ['title' => $listing['title']]) }}">
        @if ($listing['cover_url'])
            <img src="{{ $listing['cover_url'] }}" alt="{{ $listing['title'] }}" loading="lazy">
        @else
            <span class="market-card__placeholder" aria-hidden="true">
                <x-dynamic-component :component="'lucide-'.$listing['type_icon']" class="size-10" />
            </span>
        @endif
        <span class="market-card__type">
            <x-dynamic-component :component="'lucide-'.$listing['type_icon']" class="size-3.5" aria-hidden="true" />
            {{ $listing['type_label'] }}
        </span>
    </a>

    <div class="market-card__body">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase text-paw-leaf">{{ $listing['category_label'] }}</p>
                <h2 class="mt-1 text-lg font-bold leading-6">
                    <a href="{{ route('marketplace.show', $listing['slug']) }}" class="hover:text-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf">
                        {{ $listing['title'] }}
                    </a>
                </h2>
            </div>
            <strong class="shrink-0 text-lg">{{ $listing['price_label'] }}</strong>
        </div>

        @if ($listing['brand_model'])
            <p class="text-sm font-semibold text-paw-muted">{{ $listing['brand_model'] }}</p>
        @endif

        <p class="market-card__excerpt">{{ $listing['excerpt'] }}</p>

        <div class="flex flex-wrap gap-2" aria-label="{{ __('ui.suitable_pets_f64e6eef51') }}">
            @forelse (array_slice($listing['species_labels'], 0, 3) as $species)
                <span class="tag">{{ $species }}</span>
            @empty
                <span class="text-xs text-paw-muted">{{ __('ui.pet_type_not_specified_af58a4e2cc') }}</span>
            @endforelse
        </div>

        <dl class="market-card__facts">
            <div>
                <dt><x-lucide-map-pin class="size-3.5" aria-hidden="true" /> {{ __('ui.location_15b61974b2') }}</dt>
                <dd>{{ $listing['location_label'] }}</dd>
            </div>
            <div>
                <dt><x-lucide-package-check class="size-3.5" aria-hidden="true" /> {{ __('ui.availability_12f67f8539') }}</dt>
                <dd>{{ $listing['availability_label'] }} · {{ $listing['quantity'] }}</dd>
            </div>
        </dl>

        <footer class="market-card__footer">
            <div class="min-w-0 text-xs text-paw-muted">
                <span class="flex items-center gap-1 truncate font-semibold text-paw-ink">
                    {{ $listing['business_name'] ?? $listing['owner_name'] }}
                    @if ($listing['seller_verified'])
                        <x-lucide-badge-check class="size-3.5 shrink-0 text-paw-leaf" aria-label="{{ __('ui.verified_seller_8988c729d5') }}" />
                    @endif
                </span>
                <span>
                    {{ $listing['seller_type_label'] }}
                    @if ($listing['item_rating'])
                        · {{ $listing['item_rating'] }}/5 ({{ $listing['reviews_count'] }})
                    @endif
                </span>
            </div>
            <div class="flex shrink-0 gap-2">
                <x-action-control
                    :label="$listing['is_saved'] ? __('ui.saved_b5c120b316') : __('ui.save_1509f561f2')"
                    :active-label="$listing['is_saved'] ? __('ui.saved_b5c120b316') : null"
                    icon="bookmark"
                    active-icon="bookmark-check"
                    :active="$listing['is_saved']"
                    :pressed="$listing['is_saved']"
                    :endpoint="route('marketplace.actions', $listing['slug'])"
                    :payload="['action' => 'toggle-save']"
                />
                <x-action-control label="{{ __('ui.view_dcc839a401') }}" icon="arrow-right" variant="primary" :href="route('marketplace.show', $listing['slug'])" />
            </div>
        </footer>
    </div>
</article>
