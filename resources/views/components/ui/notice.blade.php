@props([
    'section' => null,
    'icon',
    'title',
    'description',
])

<section @if ($section) data-section="{{ $section }}" @endif {{ $attributes->class(['notice']) }}>
    <x-dynamic-component :component="'lucide-'.$icon" class="notice__icon" aria-hidden="true" />
    <div class="notice__content">
        <h2 class="notice__title">{{ $title }}</h2>
        <p class="notice__description">{{ $description }}</p>
        @isset($actions)
            <div class="notice__actions">
                {{ $actions }}
            </div>
        @endisset
    </div>
</section>
