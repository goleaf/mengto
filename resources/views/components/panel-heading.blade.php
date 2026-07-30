@props([
    'title' => null,
    'meta' => null,
])

<div {{ $attributes->class(['panel-heading']) }}>
    <div class="panel-heading__content">
        @isset($heading)
            {{ $heading }}
        @elseif ($title)
            <h2 class="panel-heading__title">{{ $title }}</h2>
        @endif
    </div>

    @if ($meta || isset($aside))
        <div class="panel-heading__aside">
            @if ($meta)
                <span class="panel-heading__meta">{{ $meta }}</span>
            @endif

            @isset($aside)
                {{ $aside }}
            @endisset
        </div>
    @endif
</div>
