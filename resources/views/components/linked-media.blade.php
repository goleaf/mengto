@props([
    'href' => null,
    'label' => null,
    'variant' => 'card',
    'external' => false,
])

@if ($href && is_string($label) && trim($label) !== '')
    <a
        href="{{ $href }}"
        aria-label="{{ $label }}"
        data-linked-media="linked"
        @if ($external) target="_blank" rel="noopener noreferrer" @endif
        {{ $attributes->class([
            'linked-media',
            'linked-media--card' => $variant === 'card',
            'linked-media--avatar' => $variant === 'avatar',
            'linked-media--thumbnail' => $variant === 'thumbnail',
            'linked-media--placeholder' => $variant === 'placeholder',
        ]) }}
    >
        {{ $slot }}
    </a>
@else
    <div
        data-linked-media="passive"
        {{ $attributes->class([
            'linked-media-passive',
            'linked-media-passive--card' => $variant === 'card',
            'linked-media-passive--avatar' => $variant === 'avatar',
            'linked-media-passive--thumbnail' => $variant === 'thumbnail',
            'linked-media-passive--placeholder' => $variant === 'placeholder',
        ]) }}
    >
        {{ $slot }}
    </div>
@endif
