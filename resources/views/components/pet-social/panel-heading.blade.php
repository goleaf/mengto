@props([
    'title' => null,
    'meta' => null,
])

<div {{ $attributes->class(['pc-panel-heading']) }}>
    <div class="pc-panel-heading__content">
        @isset($heading)
            {{ $heading }}
        @elseif ($title)
            <h2 class="pc-panel-heading__title">{{ $title }}</h2>
        @endif
    </div>

    @if ($meta || isset($aside))
        <div class="pc-panel-heading__aside">
            @if ($meta)
                <span class="pc-panel-heading__meta">{{ $meta }}</span>
            @endif

            @isset($aside)
                {{ $aside }}
            @endisset
        </div>
    @endif
</div>
