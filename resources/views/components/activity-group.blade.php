@props(['group', 'index'])

<section aria-labelledby="activity-group-{{ $index }}">
    <div class="border-b border-paw-line bg-paw-paper px-4 py-3 sm:px-5">
        <h2 id="activity-group-{{ $index }}" class="text-xs font-semibold uppercase text-paw-leaf">
            {{ $group['label'] }}
        </h2>
    </div>

    <div role="list" aria-label="{{ __('presentation.notifications_for', ['name' => $group['label']]) }}">
        @forelse ($group['items'] as $item)
            <x-activity-item :item="$item" />
        @empty
            <p role="listitem" class="px-5 py-6 text-sm text-paw-muted">{{ __('ui.no_activity_in_this_group') }}</p>
        @endforelse
    </div>
</section>
