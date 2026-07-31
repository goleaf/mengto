<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <livewire:forum.group-workspace :group-id="$group_id" />
        <livewire:forum.group-management :group-id="$group_id" />
    </div>
</x-app-shell>
