@props([
    'items',
    'empty' => 'No tags available.',
    'reserve' => false,
    'roomy' => false,
])

<div
    {{ $attributes->class([
        'tag-list',
        'tag-list--reserved' => $reserve,
        'tag-list--roomy' => $roomy,
    ]) }}
>
    @forelse ($items as $item)
        <span class="tag">{{ $item }}</span>
    @empty
        <span class="tag-list__empty">{{ $empty }}</span>
    @endforelse
</div>
