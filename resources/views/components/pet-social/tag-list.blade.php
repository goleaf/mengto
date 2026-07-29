@props([
    'items',
    'empty' => 'No tags available.',
    'reserve' => false,
    'roomy' => false,
])

<div
    {{ $attributes->class([
        'pc-tag-list',
        'pc-tag-list--reserved' => $reserve,
        'pc-tag-list--roomy' => $roomy,
    ]) }}
>
    @forelse ($items as $item)
        <span class="pc-tag">{{ $item }}</span>
    @empty
        <span class="pc-tag-list__empty">{{ $empty }}</span>
    @endforelse
</div>
