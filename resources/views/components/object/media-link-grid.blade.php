@props(['items', 'empty' => 'Nothing to explore yet.'])

<div role="list" {{ $attributes->class('media-link-grid') }}>
    @forelse ($items as $item)
        <div role="listitem">
            <x-object.media-link-card :item="$item" :eager="$loop->first" />
        </div>
    @empty
        <p role="listitem" class="text-sm text-paw-muted">{{ $empty }}</p>
    @endforelse
</div>
