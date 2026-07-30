@props(['summary'])

<section class="care-summary-grid" aria-label="{{ __('ui.today_s_recorded_care_summary_9059172008') }}">
    @forelse ([
        ['label' => __('ui.feedings_b44a7d3338'), 'value' => $summary['feeding_count'], 'detail' => $summary['last_feeding'], 'icon' => 'utensils'],
        ['label' => __('ui.water_7ca7dea906'), 'value' => $summary['water_count'], 'detail' => $summary['last_water'], 'icon' => 'droplets'],
        ['label' => __('ui.walks_22e4ca854b'), 'value' => $summary['walk_count'], 'detail' => $summary['last_walk'], 'icon' => 'footprints'],
        ['label' => __('ui.toilet_3e49ac277c'), 'value' => $summary['toilet_count'], 'detail' => $summary['last_toilet'], 'icon' => 'sparkles'],
        ['label' => __('ui.sleep_logged_4de0458002'), 'value' => __('presentation.minutes', ['count' => $summary['sleep_minutes']]), 'detail' => __('ui.recorded_today_3d765d35eb'), 'icon' => 'moon'],
        ['label' => __('ui.activity_38da1505ca'), 'value' => __('presentation.minutes', ['count' => $summary['activity_minutes']]), 'detail' => __('ui.play_and_training_e3d9ea430c'), 'icon' => 'dumbbell'],
        ['label' => __('ui.open_tasks_87cfa1a507'), 'value' => $summary['open_tasks'], 'detail' => __('presentation.overdue_count', ['count' => $summary['overdue_tasks']]), 'icon' => 'list-checks'],
        ['label' => __('ui.observations_f87558869b'), 'value' => $summary['unusual_count'], 'detail' => __('ui.marked_unusual_f533eb859d'), 'icon' => 'scan-heart'],
    ] as $item)
        <div>
            <x-dynamic-component :component="'lucide-'.$item['icon']" class="size-5" aria-hidden="true" />
            <span>{{ $item['label'] }}</span>
            <strong>{{ $item['value'] }}</strong>
            <small>{{ $item['detail'] }}</small>
        </div>
    @empty
        <p>{{ __('ui.no_care_facts_recorded_today_900e3cbf50') }}</p>
    @endforelse
</section>
