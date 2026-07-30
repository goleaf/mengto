<x-layout.app-shell
    :owner="$owner"
    :title="$page_title"
    :active-section="$active_section"
>
    <x-layout.page-stack>
        <x-ui.text-link
            :href="route('pet-social.groups.index')"
            icon="arrow-left"
            variant="back"
        >
            Back to groups
        </x-ui.text-link>

        <x-object.group-hero :group="$group" />

        <x-ui.tab-list
            :tabs="$tabs"
            :label="$group['name'].' sections'"
            class="group-tabs"
        />

        <x-feature.group-dashboard
            :group="$group"
            :active-tab="$active_tab"
            :content="$content"
            :membership="$membership"
            :can-view-content="$can_view_content"
            :access-gate="$access_gate"
        />
    </x-layout.page-stack>
</x-layout.app-shell>
