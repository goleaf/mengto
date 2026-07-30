@props(['listing'])

<article class="market-card">
    <a href="{{ route('marketplace.show', $listing['slug']) }}" class="market-card__media" aria-label="View {{ $listing['title'] }}">
        @if ($listing['cover_url'])
            <img src="{{ $listing['cover_url'] }}" alt="" loading="lazy">
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

        <p class="market-card__excerpt">{{ $listing['excerpt'] }}</p>

        <div class="flex flex-wrap gap-2" aria-label="Suitable pets">
            @forelse (array_slice($listing['species_labels'], 0, 3) as $species)
                <span class="tag">{{ $species }}</span>
            @empty
                <span class="text-xs text-paw-muted">Pet type not specified</span>
            @endforelse
        </div>

        <dl class="market-card__facts">
            <div>
                <dt><x-lucide-map-pin class="size-3.5" aria-hidden="true" /> Location</dt>
                <dd>{{ $listing['location_label'] }}</dd>
            </div>
            <div>
                <dt><x-lucide-sparkles class="size-3.5" aria-hidden="true" /> Condition</dt>
                <dd>{{ $listing['condition_label'] }}</dd>
            </div>
        </dl>

        <footer class="market-card__footer">
            <div class="min-w-0 text-xs text-paw-muted">
                <span class="block truncate font-semibold text-paw-ink">{{ $listing['business_name'] ?? $listing['owner_name'] }}</span>
                <span>{{ $listing['published_label'] }}</span>
            </div>
            <div class="flex shrink-0 gap-2">
                <x-action-control
                    :label="$listing['is_saved'] ? 'Saved' : 'Save'"
                    :active-label="$listing['is_saved'] ? 'Saved' : null"
                    icon="bookmark"
                    active-icon="bookmark-check"
                    :active="$listing['is_saved']"
                    :pressed="$listing['is_saved']"
                    :endpoint="route('marketplace.actions', $listing['slug'])"
                    :payload="['action' => 'toggle-save']"
                />
                <x-action-control label="View" icon="arrow-right" variant="primary" :href="route('marketplace.show', $listing['slug'])" />
            </div>
        </footer>
    </div>
</article>
