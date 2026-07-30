@props(['items', 'empty' => __('ui.details_unavailable_22d07eafc4')])

<ul role="list" {{ $attributes->class(['detail-hero__meta']) }}>
    @forelse ($items as $item)
        <x-detail-meta-item :item="$item" />
    @empty
        <li class="detail-hero__meta-empty">{{ $empty }}</li>
    @endforelse
</ul>
