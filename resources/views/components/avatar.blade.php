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
