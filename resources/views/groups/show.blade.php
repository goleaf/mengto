<x-app-shell
    :owner="$owner"
    :title="$page_title"
    :active-section="$active_section"
>
    <x-page-stack>
        <x-text-link
            :href="route('groups.index')"
            icon="arrow-left"
            variant="back"
            data-group-detail-back
        >
            {{ __('groups.detail.back_to_groups') }}
        </x-text-link>

        <x-group-hero :group="$group" />

        <x-tab-list
            :tabs="$tabs"
            :label="__('presentation.sections_for', ['name' => $group['name']])"
            class="group-tabs"
        />

        <x-group-dashboard
            :group="$group"
            :active-tab="$active_tab"
            :content="$content"
            :membership="$membership"
            :can-view-content="$can_view_content"
            :access-gate="$access_gate"
        />
    </x-page-stack>
</x-app-shell>
