@props(['items', 'empty' => 'No ranked items.'])

<ol {{ $attributes->class(['mt-4']) }}>
    @forelse ($items as $item)
        <li class="flex items-start gap-3 border-b border-paw-line py-3 first:pt-0 last:border-b-0 last:pb-0">
            <span class="grid size-7 shrink-0 place-items-center rounded-md bg-paw-paper text-xs font-semibold text-paw-leaf">{{ $loop->iteration }}</span>
            <div class="min-w-0">
                <p class="text-sm font-semibold leading-5 text-paw-ink">{{ $item['topic'] }}</p>
                <p class="mt-1 text-xs text-paw-muted">{{ $item['category'] }} · {{ $item['count'] }}</p>
            </div>
        </li>
    @empty
        <li class="text-sm text-paw-muted">{{ $empty }}</li>
    @endforelse
</ol>
