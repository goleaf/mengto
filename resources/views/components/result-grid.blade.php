@props([
    'section',
    'titleId',
    'title',
])

<section data-section="{{ $section }}" aria-labelledby="{{ $titleId }}">
    <h2 id="{{ $titleId }}" class="sr-only">{{ $title }}</h2>

    <div
        role="list"
        {{ $attributes->class(['grid gap-4 sm:grid-cols-2 xl:grid-cols-3']) }}
    >
        {{ $slot }}
    </div>
</section>
