@props([
    'place',
    'selected' => false,
    'eager' => false,
])

<article
    {{ $attributes->class([
        'place-card',
        'place-card--selected' => $selected,
        'place-card--warning' => $place['warning_count'] > 0,
    ]) }}
    data-place-card="{{ $place['key'] }}"
>
    <a href="{{ $place['detail_url'] }}" class="place-card__media" aria-label="{{ __('presentation.open_place', ['name' => $place['name']]) }}">
        <x-responsive-image
            :src="$place['image_medium']"
            :small="$place['image_small']"
            :alt="$place['image_alt']"
            width="720"
            height="540"
            sizes="(min-width: 1024px) 420px, (min-width: 640px) 50vw, 100vw"
            :eager="$eager"
        />
        <span class="place-card__category">
            <x-dynamic-component :component="'lucide-'.$place['category_icon']" class="icon icon--sm" aria-hidden="true" />
            {{ $place['category_label'] }}
        </span>
    </a>

    <div class="place-card__body">
        <div class="place-card__heading">
            <div>
                <h3><a href="{{ $place['detail_url'] }}" class="place-card__title">{{ $place['name'] }}</a></h3>
                <p class="place-card__location">{{ $place['neighborhood'] }} · {{ $place['distance_label'] }} · {{ $place['travel_label'] }}</p>
            </div>
            <x-status-badge
                :label="$place['open_label']"
                :tone="$place['status_tone']"
                size="compact"
            />
        </div>

        <p class="place-card__summary">{{ $place['summary'] }}</p>

        <div class="place-card__facts" aria-label="{{ __('ui.place_highlights_e9c48986d7') }}">
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
                <span>{{ trans_choice('presentation.active_warnings', $place['warning_count'], ['count' => $place['warning_count']]) }}</span>
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
                label="{{ __('ui.route_adc74704d6') }}"
                icon="navigation"
                variant="primary"
                size="compact"
                target="_blank"
                rel="noopener noreferrer"
            />
            @if ($place['call_url'])
                <x-action-control
                    :href="$place['call_url']"
                    label="{{ __('ui.call_d6e645b7d2') }}"
                    icon="phone"
                    variant="surface"
                    size="compact"
                />
            @endif
            <x-action-control
                :href="$place['detail_url']"
                label="{{ __('ui.details_45989de49f') }}"
                icon="arrow-right"
                variant="ghost"
                size="compact"
            />
        </div>
    </div>
</article>
