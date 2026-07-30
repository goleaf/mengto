@props([
    'actions',
    'title' => 'Safety controls',
])

@if ($actions !== [])
    <x-ui.content-panel
        eyebrow="Your boundaries"
        :title="$title"
        section="profile-safety"
        size="compact"
    >
        <p class="section-body text-sm leading-6 text-paw-muted">
            Blocking is mutual. Reports are private and never reveal who submitted them.
        </p>
        <x-ui.action-list
            :actions="$actions"
            label="Profile safety actions"
            size="regular"
            class="section-body"
        />
    </x-ui.content-panel>
@endif
