@props([
    'badges',
    'compact' => false,
])

<div
    role="list"
    aria-label="{{ __('ui.profile_badges_d02d047bc0') }}"
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
        <span class="text-sm text-paw-muted">{{ __('ui.no_profile_badges_yet_1094e6f23a') }}</span>
    @endforelse
</div>
