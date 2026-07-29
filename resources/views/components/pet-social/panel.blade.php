@props([
    'section' => null,
    'padded' => true,
    'clip' => false,
])

<section
    @if ($section) data-section="{{ $section }}" @endif
    {{ $attributes->class([
        'pc-panel',
        'pc-panel--padded' => $padded,
        'pc-panel--clip' => $clip,
    ]) }}
>
    {{ $slot }}
</section>
