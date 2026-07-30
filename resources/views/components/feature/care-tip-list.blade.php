@props(['tips'])

<x-layout.sidebar-section title="Care tips" section="tips">
    <x-object.sidebar-list>
        @forelse ($tips as $tip)
            <x-object.sidebar-list-item>
                <h3 class="break-words text-sm font-semibold text-paw-ink">{{ $tip['title'] }}</h3>
                <p class="mt-1 text-sm leading-6 text-paw-muted">{{ $tip['description'] }}</p>
            </x-object.sidebar-list-item>
        @empty
            <p role="listitem" class="sidebar-list__empty">No care tips today.</p>
        @endforelse
    </x-object.sidebar-list>
</x-layout.sidebar-section>
