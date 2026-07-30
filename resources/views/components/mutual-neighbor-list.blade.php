@props(['neighbors', 'count'])

<x-content-panel
    section="mutual-neighbors"
    title="{{ __('ui.mutual_neighbors_a225ce44b1') }}"
    :meta="__('presentation.total_count', ['count' => $count])"
>
    <div role="list" class="section-body">
        @forelse ($neighbors as $neighbor)
            <div role="listitem" class="flex items-center gap-3 border-b border-paw-line py-3 first:pt-0 last:border-b-0 last:pb-0">
                <x-initials-avatar
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
            <p role="listitem" class="text-sm text-paw-muted">{{ __('ui.no_mutual_neighbors_yet_f67047c219') }}</p>
        @endforelse
    </div>
</x-content-panel>
