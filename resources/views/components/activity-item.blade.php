@props(['item'])

<article
    role="listitem"
    data-activity-item
    @if ($item['unread']) data-unread="true" @endif
    @class([
        'grid grid-cols-[3rem_minmax(0,1fr)] gap-3 border-b border-paw-line px-4 py-4 last:border-b-0 sm:px-5',
        'bg-paw-mint/40' => $item['unread'],
        'bg-white' => ! $item['unread'],
    ])
>
    <x-avatar :src="$item['image']" lazy decorative />

    <div class="min-w-0">
        <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-2">
                <p class="text-xs font-semibold text-paw-coral">{{ $item['category'] }}</p>
                @if ($item['unread'])
                    <x-status-badge label="{{ __('ui.new') }}" icon="circle" tone="mint" size="compact" />
                @endif
            </div>
            <time datetime="{{ $item['datetime'] }}" class="shrink-0 text-xs font-semibold text-paw-muted">{{ $item['time'] }}</time>
        </div>

        <h3 class="mt-1 break-words text-sm font-semibold leading-5 text-paw-ink">{{ $item['title'] }}</h3>
        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ $item['body'] }}</p>
        <p class="mt-2 text-xs font-semibold leading-5 text-paw-leaf">{{ $item['context'] }}</p>
    </div>
</article>
