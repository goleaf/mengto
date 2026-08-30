<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <livewire:forum.forum-event-workspace
        :event-id="$event_id"
        :workspace-mode="$workspace_mode ?? 'detail'"
    />
</x-app-shell>
