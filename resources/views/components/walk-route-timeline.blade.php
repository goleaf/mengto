@props(['steps'])

<section aria-label="Walk routine" {{ $attributes->class('walk-timeline') }}>
    <ol class="walk-timeline__list">
        @forelse ($steps as $step)
            <x-walk-route-step :step="$step" />
        @empty
            <li class="walk-timeline__empty">Route details are still open.</li>
        @endforelse
    </ol>
</section>
