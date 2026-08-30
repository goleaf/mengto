@props([
    'actions',
    'title' => __('ui.safety_controls'),
    'copy' => [],
])

@if ($actions !== [])
    <x-content-panel
        :eyebrow="$copy['eyebrow'] ?? __('ui.your_boundaries')"
        :title="$copy['title'] ?? $title"
        :icon="$copy['icon'] ?? null"
        section="profile-safety"
        size="compact"
        {{ $attributes }}
    >
        <p class="section-body text-sm leading-6 text-paw-muted">
            {{ $copy['description'] ?? __('ui.blocking_is_mutual_reports_are_private_and_never_reveal_who_submitted_them') }}
        </p>
        <x-action-list
            :actions="$actions"
            :label="$copy['actions_label'] ?? __('ui.profile_safety_actions')"
            size="regular"
            class="section-body"
        />
    </x-content-panel>
@endif
