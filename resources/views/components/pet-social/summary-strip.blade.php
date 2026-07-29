@props([
    'items',
    'label',
    'icons' => [],
    'empty' => 'Summary unavailable.',
])

<section aria-label="{{ $label }}" {{ $attributes->class('pc-summary-strip') }}>
    @forelse ($items as $item)
        <div class="pc-summary-stat">
            <div class="pc-summary-stat__label">
                @if (isset($icons[$loop->index]))
                    <x-dynamic-component
                        :component="'lucide-'.$icons[$loop->index]"
                        class="pc-icon pc-icon--sm"
                        aria-hidden="true"
                    />
                @endif
                <span>{{ $item['label'] }}</span>
            </div>
            <p class="pc-summary-stat__value">{{ $item['value'] }}</p>
            <p class="pc-summary-stat__detail">{{ $item['detail'] }}</p>
        </div>
    @empty
        <p class="pc-summary-strip__empty">{{ $empty }}</p>
    @endforelse
</section>
