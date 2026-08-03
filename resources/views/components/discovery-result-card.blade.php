@props(['item', 'query', 'activeCategory', 'eager' => false])

<article role="listitem" data-discover-result="{{ $item['category'] }}:{{ $item['key'] }}" class="discovery-result-card panel panel--clip">
    <x-linked-media
        :href="$item['url']"
        :label="__('discovery.actions.open_named', ['name' => $item['title']])"
        variant="card"
        class="discovery-result-card__media"
    >
        @if ($item['image'])
            <x-responsive-image
                :src="$item['image']"
                :alt="$item['image_alt']"
                :width="1200"
                :height="760"
                sizes="(min-width: 1280px) 30vw, (min-width: 640px) 46vw, calc(100vw - 2rem)"
                :eager="$eager"
                class="h-full w-full object-cover"
            />
        @else
            <span class="discovery-result-card__placeholder">
                <x-dynamic-component :component="'lucide-'.$item['icon']" class="icon" aria-hidden="true" />
            </span>
        @endif
    </x-linked-media>

    <div class="discovery-result-card__body">
        <div class="discovery-result-card__topline">
            <p class="discovery-result-card__category">{{ $item['category_label'] }}</p>
            <x-status-badge :label="$item['status']" :tone="$item['status_tone']" />
        </div>

        <div class="min-w-0">
            <h3 class="discovery-result-card__title">
                <x-text-link :href="$item['url']">{{ $item['title'] }}</x-text-link>
            </h3>
            <p class="discovery-result-card__description">{{ $item['description'] }}</p>
        </div>

        <ul class="discovery-result-card__meta" aria-label="{{ __('discovery.meta.label') }}">
            @foreach ($item['meta'] as $meta)
                <li>
                    <x-dynamic-component :component="'lucide-'.$meta['icon']" class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $meta['label'] }}</span>
                </li>
            @endforeach
        </ul>

        <p class="discovery-result-card__reason">
            <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
            <span><strong>{{ __('discovery.reasons.label') }}</strong> {{ $item['reason'] }}</span>
        </p>

        <div class="discovery-result-card__actions">
            <x-action-control
                :href="$item['url']"
                label="{{ __('discovery.actions.open') }}"
                icon="arrow-right"
                variant="primary"
                size="compact"
            />
            <x-action-control
                endpoint="{{ route('discover.preferences.store') }}"
                label="{{ __('discovery.actions.hide_item') }}"
                icon="eye-off"
                variant="quiet"
                size="compact"
                :payload="[
                    'action' => 'hide',
                    'scope' => 'item',
                    'category' => $item['category'],
                    'target_key' => $item['key'],
                    'reason_code' => 'not_relevant',
                    'return_category' => $activeCategory,
                    'return_q' => $query,
                ]"
            />
        </div>
    </div>
</article>
