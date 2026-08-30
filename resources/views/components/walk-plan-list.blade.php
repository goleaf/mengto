@props(['plans'])

<section aria-labelledby="walk-plan-list-title">
    <h2 id="walk-plan-list-title" class="sr-only">{{ __('ui.walk_plans') }}</h2>

    <div role="list" {{ $attributes->class('walk-list') }}>
        @forelse ($plans as $plan)
            <x-walk-plan-card :plan="$plan" :eager="$loop->first" role="listitem" />
        @empty
            <x-empty-state
                icon="calendar-search"
                title="{{ __('ui.no_walk_plans_found') }}"
                description="{{ __('ui.start_a_new_plan_to_keep_the_route_pace_and_neighbor_details_together') }}"
                :href="route('compose', 'walk')"
                action-label="{{ __('ui.create_a_walk_plan') }}"
                action-icon="calendar-plus"
                role="listitem"
            />
        @endforelse
    </div>
</section>
