@props(['place'])

<section class="place-hero">
    <div class="place-hero__media">
        <img
            src="{{ $place['image_medium'] }}"
            srcset="{{ $place['image_small'] }} 720w, {{ $place['image_medium'] }} 1200w, {{ $place['image'] }} 1600w"
            sizes="(max-width: 900px) 100vw, 58vw"
            alt="{{ $place['image_alt'] }}"
            width="1200"
            height="750"
        >
        <div class="place-hero__badges">
            <x-status-badge
                :label="$place['category_label']"
                :icon="$place['category_icon']"
                :tone="$place['category_tone']"
            />
            @if ($place['sponsored'])
                <x-status-badge label="{{ __('ui.paid_promotion_854a52cc20') }}" icon="badge-dollar-sign" tone="warning" />
            @endif
        </div>
    </div>

    <div class="place-hero__content">
        <div class="place-hero__title">
            <div>
                <p>{{ $place['neighborhood'] }} · {{ $place['city'] }}</p>
                <h1>{{ $place['name'] }}</h1>
            </div>
            <x-status-badge
                :label="$place['open_label']"
                :tone="$place['status_tone']"
            />
        </div>

        <p class="place-hero__summary">{{ $place['summary'] }}</p>

        <dl class="place-hero__meta">
            <div>
                <dt><x-lucide-map-pin class="icon icon--sm" aria-hidden="true" /> {{ __('ui.address_56ef8f2095') }}</dt>
                <dd>{{ $place['address'] }}</dd>
            </div>
            <div>
                <dt><x-lucide-navigation class="icon icon--sm" aria-hidden="true" /> {{ __('ui.travel_d2b98fb537') }}</dt>
                <dd>{{ $place['distance_label'] }} · {{ $place['travel_label'] }}</dd>
            </div>
            <div>
                <dt><x-lucide-star class="icon icon--sm" aria-hidden="true" /> {{ __('ui.reviews_84cb7871b7') }}</dt>
                <dd>{{ $place['rating_label'] }}</dd>
            </div>
            <div>
                <dt><x-lucide-badge-check class="icon icon--sm" aria-hidden="true" /> {{ __('ui.data_cec3a9b89b') }}</dt>
                <dd>{{ $place['verification']['label'] }} · {{ $place['data_freshness'] }}</dd>
            </div>
        </dl>

        @if ($place['warning_count'] > 0)
            <a href="{{ $place['detail_url'].'?tab=updates' }}" class="place-hero__warning">
                <x-lucide-triangle-alert class="icon" aria-hidden="true" />
                <span>
                    <strong>{{ trans_choice('presentation.active_warnings', $place['warning_count'], ['count' => $place['warning_count']]) }}</strong>
                    {{ __('ui.review_current_conditions_before_travel_e420a9395b') }}
                </span>
            </a>
        @endif

        <div class="place-hero__actions">
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
                :endpoint="route('actions.perform')"
                :payload="$place['follow_action']['payload']"
                :label="$place['follow_action']['label']"
                :icon="$place['follow_action']['icon']"
                :active="$place['follow_action']['active']"
                :pressed="$place['follow_action']['active']"
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
        </div>
    </div>
</section>
