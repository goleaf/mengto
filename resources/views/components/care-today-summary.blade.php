@props(['summary'])

<section class="care-summary-grid" aria-label="Today's recorded care summary">
    @forelse ([
        ['label' => 'Feedings', 'value' => $summary['feeding_count'], 'detail' => $summary['last_feeding'], 'icon' => 'utensils'],
        ['label' => 'Water', 'value' => $summary['water_count'], 'detail' => $summary['last_water'], 'icon' => 'droplets'],
        ['label' => 'Walks', 'value' => $summary['walk_count'], 'detail' => $summary['last_walk'], 'icon' => 'footprints'],
        ['label' => 'Toilet', 'value' => $summary['toilet_count'], 'detail' => $summary['last_toilet'], 'icon' => 'sparkles'],
        ['label' => 'Sleep logged', 'value' => $summary['sleep_minutes'].' min', 'detail' => 'Recorded today', 'icon' => 'moon'],
        ['label' => 'Activity', 'value' => $summary['activity_minutes'].' min', 'detail' => 'Play and training', 'icon' => 'dumbbell'],
        ['label' => 'Open tasks', 'value' => $summary['open_tasks'], 'detail' => $summary['overdue_tasks'].' overdue', 'icon' => 'list-checks'],
        ['label' => 'Observations', 'value' => $summary['unusual_count'], 'detail' => 'Marked unusual', 'icon' => 'scan-heart'],
    ] as $item)
        <div>
            <x-dynamic-component :component="'lucide-'.$item['icon']" class="size-5" aria-hidden="true" />
            <span>{{ $item['label'] }}</span>
            <strong>{{ $item['value'] }}</strong>
            <small>{{ $item['detail'] }}</small>
        </div>
    @empty
        <p>No care facts recorded today.</p>
    @endforelse
</section>
