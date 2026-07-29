@props(['item'])

<li {{ $attributes->class(['pc-detail-hero__meta-item']) }}>
    <x-dynamic-component :component="'lucide-'.$item['icon']" class="pc-icon pc-icon--sm" aria-hidden="true" />

    @if (isset($item['datetime']))
        <time datetime="{{ $item['datetime'] }}" aria-label="{{ $item['aria_label'] ?? $item['label'] }}">
            {{ $item['label'] }}
        </time>
    @else
        <span>{{ $item['label'] }}</span>
    @endif
</li>
