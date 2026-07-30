@props(['items', 'empty' => __('ui.nothing_to_explore_yet_e51c166531')])

<div role="list" {{ $attributes->class('media-link-grid') }}>
    @forelse ($items as $item)
        <div role="listitem">
            <x-media-link-card :item="$item" :eager="$loop->first" />
        </div>
    @empty
        <p role="listitem" class="text-sm text-paw-muted">{{ $empty }}</p>
    @endforelse
</div>
