@props([
    'src',
    'alt',
    'width',
    'height',
    'small' => null,
    'medium' => null,
    'smallWidth' => 576,
    'mediumWidth' => 900,
    'sizes' => '100vw',
    'eager' => false,
])

@php
    $sourceCandidates = array_filter([
        $small ? "{$small} {$smallWidth}w" : null,
        $medium ? "{$medium} {$mediumWidth}w" : null,
    ]);
    $hasResponsiveSources = $sourceCandidates !== [];

    if ($hasResponsiveSources) {
        $sourceCandidates[] = "{$src} {$width}w";
    }

    $fallbackSource = $small ?? $medium ?? $src;
@endphp

<img
    src="{{ $fallbackSource }}"
    @if ($hasResponsiveSources)
        srcset="{{ implode(', ', $sourceCandidates) }}"
        sizes="{{ $sizes }}"
    @endif
    alt="{{ $alt }}"
    width="{{ $width }}"
    height="{{ $height }}"
    loading="{{ $eager ? 'eager' : 'lazy' }}"
    @if ($eager) fetchpriority="high" @endif
    decoding="async"
    {{ $attributes }}
>
