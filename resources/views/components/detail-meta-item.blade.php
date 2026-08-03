@props(['item'])

<li {{ $attributes->class(['detail-hero__meta-item']) }}>
    <x-ui-icon size="sm" :name="$item['icon']" />

    @if (isset($item['datetime']))
        <time datetime="{{ $item['datetime'] }}" aria-label="{{ $item['aria_label'] ?? $item['label'] }}">
            {{ $item['label'] }}
        </time>
    @else
        <span>{{ $item['label'] }}</span>
    @endif
</li>
