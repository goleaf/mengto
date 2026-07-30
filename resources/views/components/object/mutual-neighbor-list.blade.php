@props(['neighbors', 'count'])

<x-ui.content-panel
    section="mutual-neighbors"
    title="Mutual neighbors"
    :meta="$count.' total'"
>
    <div role="list" class="section-body">
        @forelse ($neighbors as $neighbor)
            <div role="listitem" class="flex items-center gap-3 border-b border-paw-line py-3 first:pt-0 last:border-b-0 last:pb-0">
                <x-ui.initials-avatar
                    :initials="$neighbor['initials']"
                    :tone="$neighbor['tone']"
                    size="regular"
                />

                <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-paw-ink">{{ $neighbor['name'] }}</h3>
                    <p class="mt-1 text-xs text-paw-muted">{{ $neighbor['context'] }}</p>
                </div>
            </div>
        @empty
            <p role="listitem" class="text-sm text-paw-muted">No mutual neighbors yet.</p>
        @endforelse
    </div>
</x-ui.content-panel>
