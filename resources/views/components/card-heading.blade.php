@props([
    'title',
    'href' => null,
    'level' => 3,
    'spacing' => 'regular',
])

@if ((string) $level === '2')
    <h2
        data-card-heading
        {{ $attributes->class([
            'min-w-0 break-words text-lg font-semibold leading-6 text-paw-ink',
            'mt-1.5' => $spacing === 'compact',
            'mt-2' => $spacing === 'regular',
            'mt-3' => $spacing === 'relaxed',
        ]) }}
    >
        <x-optional-link :href="$href">{{ (string) $title }}</x-optional-link>
    </h2>
@else
    <h3
        data-card-heading
        {{ $attributes->class([
            'min-w-0 break-words text-lg font-semibold leading-6 text-paw-ink',
            'mt-1.5' => $spacing === 'compact',
            'mt-2' => $spacing === 'regular',
            'mt-3' => $spacing === 'relaxed',
        ]) }}
    >
        <x-optional-link :href="$href">{{ (string) $title }}</x-optional-link>
    </h3>
@endif
