@props([
    'items',
    'label',
    'empty' => __('ui.summary_unavailable_3a2c4e48c8'),
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
        'stat-grid--four' => count($items) === 4,
    ]) }}
>
    @forelse ($items as $item)
        <div class="stat-grid__item">
            <dt class="stat-grid__label">
                @if (isset($icons[$loop->index]))
                    <x-dynamic-component
                        :component="'lucide-'.$icons[$loop->index]"
                        class="icon icon--sm"
                        aria-hidden="true"
                    />
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
