@props([
    'actions',
    'label' => __('ui.actions'),
    'size' => 'profile',
])

<div
    role="group"
    aria-label="{{ $label }}"
    {{ $attributes->class(['profile-actions']) }}
>
    @forelse ($actions as $action)
        <x-action-control
            :label="$action['label']"
            :icon="$action['icon'] ?? null"
            :variant="$action['variant'] ?? 'paper'"
            :size="$action['size'] ?? $size"
            :endpoint="$action['endpoint'] ?? null"
            :payload="$action['payload'] ?? []"
            :href="$action['href'] ?? null"
            :active="$action['active'] ?? false"
            :active-label="$action['active_label'] ?? null"
            :active-icon="$action['active_icon'] ?? null"
            :pressed="$action['pressed'] ?? null"
        />
    @empty
        <span class="text-sm text-paw-muted">{{ __('ui.no_actions_available') }}</span>
    @endforelse
</div>
