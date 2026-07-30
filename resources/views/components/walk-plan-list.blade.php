@props(['plans'])

<section aria-labelledby="walk-plan-list-title">
    <h2 id="walk-plan-list-title" class="sr-only">Walk plans</h2>

    <div role="list" {{ $attributes->class('walk-list') }}>
        @forelse ($plans as $plan)
            <x-walk-plan-card :plan="$plan" :eager="$loop->first" role="listitem" />
        @empty
            <x-empty-state
                icon="calendar-search"
                title="No walk plans found"
                description="Start a new plan to keep the route, pace, and neighbor details together."
                :href="route('compose', 'walk')"
                action-label="Create a walk plan"
                action-icon="calendar-plus"
                role="listitem"
            />
        @endforelse
    </div>
</section>
