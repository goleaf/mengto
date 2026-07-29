@props(['conversation'])

<article
    role="listitem"
    @if ($conversation['selected']) data-selected="true" aria-current="true" @endif
    @class([
        'grid grid-cols-[3rem_minmax(0,1fr)_auto] gap-3 border-b border-l-4 border-paw-line px-4 py-4 last:border-b-0',
        'border-l-paw-leaf bg-paw-mint/60' => $conversation['selected'],
        'border-l-transparent bg-white' => ! $conversation['selected'],
    ])
>
    <x-pet-social.avatar :src="$conversation['image']" lazy decorative />

    <div class="min-w-0">
        <h3 class="truncate text-sm font-semibold text-paw-ink">{{ $conversation['name'] }}</h3>
        <p class="mt-0.5 truncate text-xs font-semibold text-paw-coral">{{ $conversation['pet'] }}</p>
        <p class="mt-2 line-clamp-2 text-xs leading-5 text-paw-muted">{{ $conversation['preview'] }}</p>
    </div>

    <div class="flex flex-col items-end gap-2">
        <time datetime="{{ $conversation['datetime'] }}" class="whitespace-nowrap text-[0.65rem] font-semibold text-paw-muted">
            {{ $conversation['time'] }}
        </time>

        @if ($conversation['unread'] > 0)
            <span class="grid size-5 place-items-center rounded-full bg-paw-leaf text-[0.65rem] font-semibold text-white" aria-label="{{ $conversation['unread'] }} unread messages">
                {{ $conversation['unread'] }}
            </span>
        @endif
    </div>
</article>
