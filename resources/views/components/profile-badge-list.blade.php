@props([
    'badges',
    'compact' => false,
])

<div
    role="list"
    aria-label="Profile badges"
    {{ $attributes->class([
        'profile-badges',
        'profile-badges--compact' => $compact,
    ]) }}
>
    @forelse ($badges as $badge)
        <x-status-badge
            role="listitem"
            :label="$badge['label']"
            :icon="$badge['icon']"
            :tone="$badge['tone'] ?? 'surface'"
            :size="$compact ? 'compact' : 'regular'"
        />
    @empty
        <span class="text-sm text-paw-muted">No profile badges yet.</span>
    @endforelse
</div>
