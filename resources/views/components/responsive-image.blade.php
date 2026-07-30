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
