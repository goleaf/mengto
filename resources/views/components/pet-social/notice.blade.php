@props([
    'section',
    'icon',
    'title',
    'description',
])

<section data-section="{{ $section }}" {{ $attributes->class(['pc-notice']) }}>
    <x-dynamic-component :component="'lucide-'.$icon" class="pc-notice__icon" aria-hidden="true" />
    <div class="pc-notice__content">
        <h2 class="pc-notice__title">{{ $title }}</h2>
        <p class="pc-notice__description">{{ $description }}</p>
    </div>
</section>
