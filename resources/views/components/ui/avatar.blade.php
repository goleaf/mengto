@props([
    'src',
    'alt' => '',
    'size' => 'compact',
    'shape' => 'circle',
    'lazy' => false,
    'decorative' => false,
])

@php
    $dimension = match ($size) {
        'header' => 40,
        'thread' => 44,
        'profile' => 64,
        default => 48,
    };
@endphp

<img
    src="{{ $src }}"
    alt="{{ $decorative ? '' : $alt }}"
    width="{{ $dimension }}"
    height="{{ $dimension }}"
    @if ($lazy) loading="lazy" @endif
    decoding="async"
    @if ($decorative) aria-hidden="true" @endif
    {{ $attributes->class([
        'avatar',
        'avatar--'.$size,
        'avatar--'.$shape,
    ]) }}
>
