@props(['step'])

<li class="walk-step">
    <span class="walk-step__icon">
        <x-dynamic-component :component="'lucide-'.$step['icon']" class="icon icon--sm" aria-hidden="true" />
    </span>
    <span class="walk-step__content">
        <span class="walk-step__label">{{ $step['label'] }}</span>
        <span class="walk-step__title">{{ $step['title'] }}</span>
    </span>
</li>
