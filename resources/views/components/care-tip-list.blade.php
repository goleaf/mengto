@props(['tips'])

<x-sidebar-section title="{{ __('ui.care_tips') }}" section="tips">
    <x-sidebar-list>
        @forelse ($tips as $tip)
            <x-sidebar-list-item>
                <h3 class="break-words text-sm font-semibold text-paw-ink">{{ $tip['title'] }}</h3>
                <p class="mt-1 text-sm leading-6 text-paw-muted">{{ $tip['description'] }}</p>
            </x-sidebar-list-item>
        @empty
            <p role="listitem" class="sidebar-list__empty">{{ __('ui.no_care_tips_today') }}</p>
        @endforelse
    </x-sidebar-list>
</x-sidebar-section>
