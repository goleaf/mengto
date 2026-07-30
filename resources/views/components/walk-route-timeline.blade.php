@props(['steps'])

<section aria-label="{{ __('ui.walk_routine_1a1f1975d5') }}" {{ $attributes->class('walk-timeline') }}>
    <ol class="walk-timeline__list">
        @forelse ($steps as $step)
            <x-walk-route-step :step="$step" />
        @empty
            <li class="walk-timeline__empty">{{ __('ui.route_details_are_still_open_62cc19ebd8') }}</li>
        @endforelse
    </ol>
</section>
