@props([
    'summary',
    'connections',
])

<section class="connection-dashboard" aria-label="{{ __('ui.subscriptions_and_recommendations_11ff7fb78e') }}">
    <x-summary-strip
        :items="$summary['stats']"
        label="{{ __('ui.connection_summary_de5459f742') }}"
        :icons="['user-check', 'users-round', 'inbox', 'star']"
        :columns="4"
    />

    <x-tab-list
        :tabs="$connections['tabs']"
        label="{{ __('ui.connection_views_8011876901') }}"
    />

    @if ($connections['last_dismissed'])
        <div class="connection-undo" role="status">
            <div>
                <x-lucide-eye-off class="icon icon--sm" aria-hidden="true" />
                <p>{{ __('presentation.hidden_from_recommendations', ['name' => $connections['last_dismissed']['name']]) }}</p>
            </div>
            <x-action-control
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
                <p>{{ __('presentation.blocked_connection', ['name' => $connections['last_blocked']['name']]) }}</p>
            </div>
            <x-action-control
                :label="$connections['last_blocked']['action']['label']"
                :icon="$connections['last_blocked']['action']['icon']"
                :endpoint="$connections['last_blocked']['action']['endpoint']"
                :payload="$connections['last_blocked']['action']['payload']"
                :variant="$connections['last_blocked']['action']['variant']"
                size="regular"
            />
        </div>
    @endif

    <x-connection-toolbar :connections="$connections" />

    @if ($connections['tab'] === 'recommendations')
        <x-recommendation-grid
            :items="$connections['items']"
            :empty="$connections['empty']"
        />
    @else
        <x-connection-list
            :items="$connections['items']"
            :empty="$connections['empty']"
        />
    @endif
</section>
