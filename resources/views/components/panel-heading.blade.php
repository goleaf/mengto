@props([
    'title' => null,
    'meta' => null,
    'icon' => null,
])

<div {{ $attributes->class(['panel-heading']) }}>
    <div class="panel-heading__content">
        @isset($heading)
            {{ $heading }}
        @elseif ($title)
            <h2 @class(['panel-heading__title', 'panel-heading__title--with-icon' => $icon])>
                @if ($icon)
                    <x-ui-icon size="sm" :name="$icon" />
                    <span>{{ $title }}</span>
                @else
                    {{ $title }}
                @endif
            </h2>
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
