@props(['tips'])

<x-pet-social.sidebar-section title="Care tips" section="tips">
    <div role="list">
        @forelse ($tips as $tip)
            <article role="listitem" class="border-b border-paw-line py-4 first:pt-0 last:border-b-0 last:pb-0">
                <h3 class="break-words text-sm font-semibold text-paw-ink">{{ $tip['title'] }}</h3>
                <p class="mt-1 text-sm leading-6 text-paw-muted">{{ $tip['description'] }}</p>
            </article>
        @empty
            <p role="listitem" class="text-sm text-paw-muted">No care tips today.</p>
        @endforelse
    </div>
</x-pet-social.sidebar-section>
