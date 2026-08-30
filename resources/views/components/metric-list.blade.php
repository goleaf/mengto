@props(['items', 'empty' => __('ui.metrics_unavailable')])

<dl {{ $attributes->class(['mt-4 grid gap-3']) }}>
    @forelse ($items as $item)
        <div class="grid grid-cols-[2.5rem_minmax(0,1fr)] gap-x-4 border-b border-paw-line pb-3 last:border-b-0 last:pb-0">
            <dt class="col-start-2 text-sm font-semibold text-paw-ink">{{ $item['label'] }}</dt>
            <dd class="col-start-1 row-span-2 row-start-1 self-center text-xl font-semibold text-paw-ink">{{ $item['value'] }}</dd>
            <dd class="col-start-2 mt-0.5 text-xs text-paw-muted">{{ $item['detail'] }}</dd>
        </div>
    @empty
        <div>
            <dt class="sr-only">{{ __('ui.metrics') }}</dt>
            <dd class="text-sm text-paw-muted">{{ $empty }}</dd>
        </div>
    @endforelse
</dl>
