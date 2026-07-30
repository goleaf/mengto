@props([
    'section' => null,
    'padded' => true,
    'clip' => false,
])

<section
    @if ($section) data-section="{{ $section }}" @endif
    {{ $attributes->class([
        'panel',
        'panel--padded' => $padded,
        'panel--clip' => $clip,
    ]) }}
>
    {{ $slot }}
</section>
