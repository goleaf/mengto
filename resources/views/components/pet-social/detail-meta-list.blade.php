@props(['items', 'empty' => 'Details unavailable.'])

<ul role="list" {{ $attributes->class(['pc-detail-hero__meta']) }}>
    @forelse ($items as $item)
        <x-pet-social.detail-meta-item :item="$item" />
    @empty
        <li class="pc-detail-hero__meta-empty">{{ $empty }}</li>
    @endforelse
</ul>
