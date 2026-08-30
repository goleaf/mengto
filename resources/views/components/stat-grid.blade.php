@props([
    'items',
    'label',
    'empty' => __('ui.summary_unavailable'),
    'icons' => [],
    'variant' => 'profile',
    'tone' => 'leaf',
    'large' => false,
])

<dl
    aria-label="{{ $label }}"
    {{ $attributes->class([
        'stat-grid',
        'stat-grid--'.$variant,
        'stat-grid--large' => $large,
        'stat-grid--muted' => $tone === 'muted',
        'stat-grid--two' => count($items) === 2,
        'stat-grid--four' => count($items) === 4,
    ]) }}
>
    @forelse ($items as $item)
        <div class="stat-grid__item">
            <dt class="stat-grid__label">
                @if (isset($icons[$loop->index]))
                    <x-ui-icon size="sm" :name="$icons[$loop->index]" />
                @endif
                <span>{{ $item['label'] }}</span>
            </dt>
            <dd class="stat-grid__value">{{ $item['value'] }}</dd>

            @if (isset($item['detail']))
                <dd class="stat-grid__detail">{{ $item['detail'] }}</dd>
            @endif
        </div>
    @empty
        <div class="stat-grid__empty">{{ $empty }}</div>
    @endforelse
</dl>
