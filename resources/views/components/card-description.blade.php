@props(['spacing' => 'regular'])

<p
    data-card-description
    {{ $attributes->class([
        'min-w-0 break-words text-pretty text-sm leading-6 text-paw-muted',
        'mt-0' => $spacing === 'none',
        'mt-1' => $spacing === 'compact',
        'mt-2' => $spacing === 'regular',
        'mt-3' => $spacing === 'relaxed',
    ]) }}
>
    {{ $slot }}
</p>
