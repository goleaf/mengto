@props([
    'items' => [],
    'empty' => 'No details available.',
    'strong' => false,
])

<dl {{ $attributes->class(['pc-definition-list']) }}>
    @forelse ($items as $item)
        <div class="pc-definition-list__item">
            <dt class="pc-definition-list__term">{{ $item['label'] }}</dt>
            <dd @class([
                'pc-definition-list__value',
                'pc-definition-list__value--strong' => $strong,
            ])>
                {{ $item['value'] }}
            </dd>
        </div>
    @empty
        <div>
            <dt class="sr-only">Details</dt>
            <dd class="text-sm text-paw-muted">{{ $empty }}</dd>
        </div>
    @endforelse
</dl>
