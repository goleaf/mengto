@props([
    'icon',
    'title',
    'description',
])

<div {{ $attributes->class(['pc-callout']) }}>
    <span class="pc-callout__icon" aria-hidden="true">
        <x-dynamic-component :component="'lucide-'.$icon" class="pc-icon" />
    </span>
    <div class="pc-callout__content">
        <h3 class="pc-callout__title">{{ $title }}</h3>
        <p class="pc-callout__description">{{ $description }}</p>
    </div>
</div>
