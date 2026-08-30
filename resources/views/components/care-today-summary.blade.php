@props(['summary'])

<section class="care-summary-grid" aria-label="{{ __('ui.today_s_recorded_care_summary') }}">
    @forelse ([
        ['label' => __('ui.feedings'), 'value' => $summary['feeding_count'], 'detail' => $summary['last_feeding'], 'icon' => 'utensils'],
        ['label' => __('ui.water'), 'value' => $summary['water_count'], 'detail' => $summary['last_water'], 'icon' => 'droplets'],
        ['label' => __('ui.walks'), 'value' => $summary['walk_count'], 'detail' => $summary['last_walk'], 'icon' => 'footprints'],
        ['label' => __('ui.toilet'), 'value' => $summary['toilet_count'], 'detail' => $summary['last_toilet'], 'icon' => 'sparkles'],
        ['label' => __('ui.sleep_logged'), 'value' => __('presentation.minutes', ['count' => $summary['sleep_minutes']]), 'detail' => __('ui.recorded_today'), 'icon' => 'moon'],
        ['label' => __('ui.activity'), 'value' => __('presentation.minutes', ['count' => $summary['activity_minutes']]), 'detail' => __('ui.play_and_training'), 'icon' => 'dumbbell'],
        ['label' => __('ui.open_tasks'), 'value' => $summary['open_tasks'], 'detail' => __('presentation.overdue_count', ['count' => $summary['overdue_tasks']]), 'icon' => 'list-checks'],
        ['label' => __('ui.observations'), 'value' => $summary['unusual_count'], 'detail' => __('ui.marked_unusual'), 'icon' => 'scan-heart'],
    ] as $item)
        <div>
            <x-ui-icon size="lg" :name="$item['icon']" />
            <span>{{ $item['label'] }}</span>
            <strong>{{ $item['value'] }}</strong>
            <small>{{ $item['detail'] }}</small>
        </div>
    @empty
        <p>{{ __('ui.no_care_facts_recorded_today') }}</p>
    @endforelse
</section>
