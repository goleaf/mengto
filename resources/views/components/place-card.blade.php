@props([
    'place',
    'selected' => false,
])

<article
    {{ $attributes->class([
        'place-card',
        'place-card--selected' => $selected,
        'place-card--warning' => $place['warning_count'] > 0,
    ]) }}
    data-place-card="{{ $place['key'] }}"
>
    <a href="{{ $place['detail_url'] }}" class="place-card__media" aria-label="Open {{ $place['name'] }}">
        <img
            src="{{ $place['image_small'] }}"
            srcset="{{ $place['image_small'] }} 720w, {{ $place['image_medium'] }} 1200w"
            sizes="(max-width: 767px) 100vw, 340px"
            alt="{{ $place['image_alt'] }}"
            width="720"
            height="540"
            loading="lazy"
        >
        <span class="place-card__category">
            <x-dynamic-component :component="'lucide-'.$place['category_icon']" class="icon icon--sm" aria-hidden="true" />
            {{ $place['category_label'] }}
        </span>
    </a>

    <div class="place-card__body">
        <div class="place-card__heading">
            <div>
                <a href="{{ $place['detail_url'] }}" class="place-card__title">{{ $place['name'] }}</a>
                <p class="place-card__location">{{ $place['neighborhood'] }} · {{ $place['distance_label'] }} · {{ $place['travel_label'] }}</p>
            </div>
            <x-status-badge
                :label="$place['open_label']"
                :tone="$place['status_tone']"
                size="compact"
            />
        </div>

        <p class="place-card__summary">{{ $place['summary'] }}</p>

        <div class="place-card__facts" aria-label="Place highlights">
            <span>
                <x-lucide-star class="icon icon--sm" aria-hidden="true" />
                {{ $place['rating_label'] }}
            </span>
            <span>
                <x-lucide-paw-print class="icon icon--sm" aria-hidden="true" />
                {{ $place['leash_policy'] }}
            </span>
            <span>
                <x-lucide-users-round class="icon icon--sm" aria-hidden="true" />
                {{ $place['crowd_label'] }}
            </span>
        </div>

        @if ($place['warning_count'] > 0)
            <a href="{{ $place['detail_url'].'?tab=updates' }}" class="place-card__warning">
                <x-lucide-triangle-alert class="icon icon--sm" aria-hidden="true" />
                <span>{{ $place['warning_count'] }} active {{ \Illuminate\Support\Str::plural('warning', $place['warning_count']) }}</span>
            </a>
        @endif

        <p class="place-card__reason">
            <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
            <span>{{ $place['recommendation_reason'] }}</span>
        </p>

        <div class="place-card__actions">
            <x-action-control
                :endpoint="route('actions.perform')"
                :payload="$place['save_action']['payload']"
                :label="$place['save_action']['label']"
                :icon="$place['save_action']['icon']"
                :active="$place['save_action']['active']"
                :pressed="$place['save_action']['active']"
                variant="surface"
                size="compact"
            />
            <x-action-control
                :href="$place['route_url']"
                label="Route"
                icon="navigation"
                variant="primary"
                size="compact"
                target="_blank"
                rel="noopener noreferrer"
            />
            <x-action-control
                :href="$place['detail_url']"
                label="Details"
                icon="arrow-right"
                variant="ghost"
                size="compact"
            />
        </div>
    </div>
</article>
