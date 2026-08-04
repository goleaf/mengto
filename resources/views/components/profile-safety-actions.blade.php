@props([
    'actions',
    'title' => __('ui.safety_controls_9a32e20ad7'),
    'copy' => [],
])

@if ($actions !== [])
    <x-content-panel
        :eyebrow="$copy['eyebrow'] ?? __('ui.your_boundaries_0b6767fe63')"
        :title="$copy['title'] ?? $title"
        :icon="$copy['icon'] ?? null"
        section="profile-safety"
        size="compact"
        {{ $attributes }}
    >
        <p class="section-body text-sm leading-6 text-paw-muted">
            {{ $copy['description'] ?? __('ui.blocking_is_mutual_reports_are_private_and_never_88b57b5337') }}
        </p>
        <x-action-list
            :actions="$actions"
            :label="$copy['actions_label'] ?? __('ui.profile_safety_actions_86908d8a5c')"
            size="regular"
            class="section-body"
        />
    </x-content-panel>
@endif
