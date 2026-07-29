@props(['groups'])

<x-pet-social.sidebar-section title="Groups for you" section="groups">
    <div role="list">
        @forelse ($groups as $group)
            <article role="listitem" class="border-b border-paw-line py-4 first:pt-0 last:border-b-0 last:pb-0">
                <h3 class="break-words text-sm font-semibold text-paw-ink">{{ $group['name'] }}</h3>
                <p class="mt-1 text-sm text-paw-muted">{{ $group['topic'] }}</p>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <x-pet-social.icon-text icon="users">{{ $group['members'] }}</x-pet-social.icon-text>
                    <x-pet-social.static-action label="Follow" icon="user-plus" variant="quiet" size="micro" />
                </div>
            </article>
        @empty
            <p role="listitem" class="text-sm text-paw-muted">No group suggestions yet.</p>
        @endforelse
    </div>
</x-pet-social.sidebar-section>
