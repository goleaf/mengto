@props([
    'items' => [],
    'empty' => 'No details available.',
    'strong' => false,
])

<dl {{ $attributes->class(['definition-list']) }}>
    @forelse ($items as $item)
        <div class="definition-list__item">
            <dt class="definition-list__term">{{ $item['label'] }}</dt>
            <dd @class([
                'definition-list__value',
                'definition-list__value--strong' => $strong,
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
