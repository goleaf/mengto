@props([
    'section',
    'titleId',
    'title',
    'columns' => 3,
])

<section data-section="{{ $section }}" aria-labelledby="{{ $titleId }}">
    <h2 id="{{ $titleId }}" class="sr-only">{{ $title }}</h2>

    <div
        role="list"
        {{ $attributes->class([
            'grid gap-4 sm:grid-cols-2',
            'xl:grid-cols-3' => $columns === 3,
            'xl:grid-cols-4' => $columns === 4,
        ]) }}
    >
        {{ $slot }}
    </div>
</section>
