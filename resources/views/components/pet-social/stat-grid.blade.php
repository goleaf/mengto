@props([
    'items',
    'label',
    'empty' => 'Summary unavailable.',
    'icons' => [],
    'variant' => 'profile',
    'tone' => 'leaf',
    'large' => false,
])

<dl
    aria-label="{{ $label }}"
    {{ $attributes->class([
        'pc-stat-grid',
        'pc-stat-grid--'.$variant,
        'pc-stat-grid--large' => $large,
        'pc-stat-grid--muted' => $tone === 'muted',
    ]) }}
>
    @forelse ($items as $item)
        <div class="pc-stat-grid__item">
            <dt class="pc-stat-grid__label">
                @if (isset($icons[$loop->index]))
                    <x-dynamic-component
                        :component="'lucide-'.$icons[$loop->index]"
                        class="pc-icon pc-icon--sm"
                        aria-hidden="true"
                    />
                @endif
                <span>{{ $item['label'] }}</span>
            </dt>
            <dd class="pc-stat-grid__value">{{ $item['value'] }}</dd>

            @if (isset($item['detail']))
                <dd class="pc-stat-grid__detail">{{ $item['detail'] }}</dd>
            @endif
        </div>
    @empty
        <div class="pc-stat-grid__empty">{{ $empty }}</div>
    @endforelse
</dl>
