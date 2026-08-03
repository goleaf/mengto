@props(['step'])

<li class="walk-step">
    <span class="walk-step__icon">
        <x-ui-icon size="sm" :name="$step['icon']" />
    </span>
    <span class="walk-step__content">
        <span class="walk-step__label">{{ $step['label'] }}</span>
        <span class="walk-step__title">{{ $step['title'] }}</span>
    </span>
</li>
