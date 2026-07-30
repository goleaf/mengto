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
            <x-ui.status-badge
                :label="$place['category_label']"
                :icon="$place['category_icon']"
                :tone="$place['category_tone']"
            />
            @if ($place['sponsored'])
                <x-ui.status-badge label="Paid promotion" icon="badge-dollar-sign" tone="warning" />
            @endif
        </div>
    </div>

    <div class="place-hero__content">
        <div class="place-hero__title">
            <div>
                <p>{{ $place['neighborhood'] }} · {{ $place['city'] }}</p>
                <h1>{{ $place['name'] }}</h1>
            </div>
            <x-ui.status-badge
                :label="$place['open_label']"
                :tone="$place['status_tone']"
            />
        </div>

        <p class="place-hero__summary">{{ $place['summary'] }}</p>

        <dl class="place-hero__meta">
            <div>
                <dt><x-lucide-map-pin class="icon icon--sm" aria-hidden="true" /> Address</dt>
                <dd>{{ $place['address'] }}</dd>
            </div>
            <div>
                <dt><x-lucide-navigation class="icon icon--sm" aria-hidden="true" /> Travel</dt>
                <dd>{{ $place['distance_label'] }} · {{ $place['travel_label'] }}</dd>
            </div>
            <div>
                <dt><x-lucide-star class="icon icon--sm" aria-hidden="true" /> Reviews</dt>
                <dd>{{ $place['rating_label'] }}</dd>
            </div>
            <div>
                <dt><x-lucide-badge-check class="icon icon--sm" aria-hidden="true" /> Data</dt>
                <dd>{{ $place['verification']['label'] }} · {{ $place['data_freshness'] }}</dd>
            </div>
        </dl>

        @if ($place['warning_count'] > 0)
            <a href="{{ $place['detail_url'].'?tab=updates' }}" class="place-hero__warning">
                <x-lucide-triangle-alert class="icon" aria-hidden="true" />
                <span>
                    <strong>{{ $place['warning_count'] }} active {{ \Illuminate\Support\Str::plural('warning', $place['warning_count']) }}</strong>
                    Review current conditions before travel.
                </span>
            </a>
        @endif

        <div class="place-hero__actions">
            <x-ui.action-control
                :endpoint="route('actions.perform')"
                :payload="$place['save_action']['payload']"
                :label="$place['save_action']['label']"
                :icon="$place['save_action']['icon']"
                :active="$place['save_action']['active']"
                :pressed="$place['save_action']['active']"
                variant="surface"
                size="compact"
            />
            <x-ui.action-control
                :endpoint="route('actions.perform')"
                :payload="$place['follow_action']['payload']"
                :label="$place['follow_action']['label']"
                :icon="$place['follow_action']['icon']"
                :active="$place['follow_action']['active']"
                :pressed="$place['follow_action']['active']"
                variant="surface"
                size="compact"
            />
            <x-ui.action-control
                :href="$place['route_url']"
                label="Route"
                icon="navigation"
                variant="primary"
                size="compact"
                target="_blank"
                rel="noopener noreferrer"
            />
            @if ($place['call_url'])
                <x-ui.action-control
                    :href="$place['call_url']"
                    label="Call"
                    icon="phone"
                    variant="surface"
                    size="compact"
                />
            @endif
        </div>
    </div>
</section>
