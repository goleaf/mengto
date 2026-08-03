@props([
    'items',
    'label',
    'icons' => [],
    'empty' => __('ui.summary_unavailable_3a2c4e48c8'),
    'columns' => 3,
])

<section
    aria-label="{{ $label }}"
    {{ $attributes->class([
        'summary-strip',
        'summary-strip--four' => $columns === 4,
    ]) }}
>
    @forelse ($items as $item)
        <div class="summary-stat">
            <div class="summary-stat__label">
                @if (isset($icons[$loop->index]))
                    <x-ui-icon size="sm" :name="$icons[$loop->index]" />
                @endif
                <span>{{ $item['label'] }}</span>
            </div>
            <p class="summary-stat__value">{{ $item['value'] }}</p>
            <p class="summary-stat__detail">{{ $item['detail'] }}</p>
        </div>
    @empty
        <p class="summary-strip__empty">{{ $empty }}</p>
    @endforelse
</section>
