@props([
    'plans',
    'title' => 'Active walk plans',
    'titleId' => 'message-walk-plans-title',
    'emptyTitle' => 'No active walk plans',
    'emptyDescription' => 'Create a plan first, then its neighbor conversation will appear in this filter.',
])

<section aria-labelledby="{{ $titleId }}" {{ $attributes->class('walk-message-summary') }}>
    <x-panel-heading
        :meta="count($plans).' '.\Illuminate\Support\Str::plural('plan', count($plans))"
    >
        <x-slot:heading>
            <h2 id="{{ $titleId }}" class="panel-heading__title">{{ $title }}</h2>
        </x-slot:heading>
        <x-slot:aside>
            <x-text-link :href="route('walks.index')" icon="arrow-right" variant="action">
                View planner
            </x-text-link>
        </x-slot:aside>
    </x-panel-heading>

    <div role="list" class="walk-message-summary__list">
        @forelse ($plans as $plan)
            <div role="listitem">
                <a
                    href="{{ route('walks.index', ['filter' => $plan['status'] === 'draft' ? 'drafts' : 'upcoming']) }}"
                    class="walk-message-item"
                >
                    <x-responsive-image
                        :src="$plan['participant']['image_small']"
                        :alt="$plan['participant']['image_alt']"
                        :width="576"
                        :height="384"
                        sizes="5rem"
                        class="walk-message-item__image"
                    />
                    <span class="walk-message-item__body">
                        <span class="walk-message-item__title">{{ $plan['title'] }}</span>
                        <span class="walk-message-item__meta">{{ $plan['date_label'] }} · {{ $plan['time_label'] }}</span>
                    </span>
                    <x-status-badge
                        :label="$plan['status_label']"
                        :icon="$plan['status_icon']"
                        :tone="$plan['status_tone']"
                    />
                </a>
            </div>
        @empty
            <x-empty-state
                icon="footprints"
                :title="$emptyTitle"
                :description="$emptyDescription"
                :href="route('compose', 'walk')"
                action-label="Create a plan"
                action-icon="calendar-plus"
                compact
                role="listitem"
            />
        @endforelse
    </div>
</section>
