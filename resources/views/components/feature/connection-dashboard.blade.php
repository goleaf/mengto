@props([
    'summary',
    'connections',
])

<section class="connection-dashboard" aria-label="Subscriptions and recommendations">
    <x-ui.summary-strip
        :items="$summary['stats']"
        label="Connection summary"
        :icons="['user-check', 'users-round', 'inbox', 'star']"
        :columns="4"
    />

    <x-ui.tab-list
        :tabs="$connections['tabs']"
        label="Connection views"
    />

    @if ($connections['last_dismissed'])
        <div class="connection-undo" role="status">
            <div>
                <x-lucide-eye-off class="icon icon--sm" aria-hidden="true" />
                <p>{{ $connections['last_dismissed']['name'] }} was hidden from recommendations.</p>
            </div>
            <x-ui.action-control
                :label="$connections['last_dismissed']['action']['label']"
                :icon="$connections['last_dismissed']['action']['icon']"
                :endpoint="$connections['last_dismissed']['action']['endpoint']"
                :payload="$connections['last_dismissed']['action']['payload']"
                :variant="$connections['last_dismissed']['action']['variant']"
                size="regular"
            />
        </div>
    @endif

    @if ($connections['last_blocked'])
        <div class="connection-undo" role="status">
            <div>
                <x-lucide-ban class="icon icon--sm" aria-hidden="true" />
                <p>{{ $connections['last_blocked']['name'] }} was blocked and removed from your connections.</p>
            </div>
            <x-ui.action-control
                :label="$connections['last_blocked']['action']['label']"
                :icon="$connections['last_blocked']['action']['icon']"
                :endpoint="$connections['last_blocked']['action']['endpoint']"
                :payload="$connections['last_blocked']['action']['payload']"
                :variant="$connections['last_blocked']['action']['variant']"
                size="regular"
            />
        </div>
    @endif

    <x-feature.connection-toolbar :connections="$connections" />

    @if ($connections['tab'] === 'recommendations')
        <x-feature.recommendation-grid
            :items="$connections['items']"
            :empty="$connections['empty']"
        />
    @else
        <x-feature.connection-list
            :items="$connections['items']"
            :empty="$connections['empty']"
        />
    @endif
</section>
