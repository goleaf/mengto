@props([
    'icon',
    'title',
    'description',
])

<div {{ $attributes->class(['callout']) }}>
    <span class="callout__icon" aria-hidden="true">
        <x-dynamic-component :component="'lucide-'.$icon" class="icon" />
    </span>
    <div class="callout__content">
        <h3 class="callout__title">{{ $title }}</h3>
        <p class="callout__description">{{ $description }}</p>
    </div>
</div>
