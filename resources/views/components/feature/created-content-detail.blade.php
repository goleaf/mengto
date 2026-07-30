@props(['owner', 'content'])

<x-layout.detail-page
    :owner="$owner"
    :title="$content['page_title']"
    :active-section="$content['active_section']"
    :section="$content['section']"
    :back-href="route($content['back_route'])"
    :back-label="$content['back_label']"
>
    <x-slot:hero>
        <x-object.detail-hero
            :detail="$content['hero']"
            :section="$content['section'].'-hero'"
            :primary-label="$content['primary']['label']"
            :primary-icon="$content['primary']['icon']"
            secondary-label="Share"
            secondary-icon="send"
            :summary-label="$content['summary_label']"
            :summary-icons="$content['summary_icons']"
            :primary-endpoint="route('actions.perform')"
            :primary-payload="[
                'action' => $content['primary']['action'],
                'target' => $content['hero']['key'],
                'label' => $content['hero']['title'],
            ]"
            :primary-active="$content['primary']['active']"
            :primary-active-label="$content['primary']['active_label']"
            :primary-active-icon="$content['primary']['active_icon']"
            :secondary-endpoint="route('actions.perform')"
            :secondary-payload="[
                'action' => 'share',
                'target' => $content['hero']['key'],
                'label' => $content['hero']['title'],
            ]"
        />
    </x-slot:hero>

    <x-slot:main>
        <x-ui.content-panel
            :eyebrow="$content['about']['eyebrow']"
            :title="$content['about']['title']"
            :section="$content['section'].'-about'"
        >
            <x-ui.section-copy :text="$content['about']['copy']" />
        </x-ui.content-panel>

        <x-ui.content-panel
            eyebrow="Good first steps"
            title="Help the connection start well"
            :section="$content['section'].'-guidance'"
        >
            <x-ui.icon-list :items="$content['guidance']" class="section-body" />
        </x-ui.content-panel>
    </x-slot:main>

    <x-slot:sidebar>
        <x-ui.content-panel
            title="At a glance"
            :section="$content['section'].'-facts'"
        >
            <x-ui.definition-list :items="$content['facts']" strong class="section-body" />
        </x-ui.content-panel>

        <x-ui.notice
            :section="$content['section'].'-notice'"
            :icon="$content['notice']['icon']"
            :title="$content['notice']['title']"
            :description="$content['notice']['description']"
        />
    </x-slot:sidebar>
</x-layout.detail-page>
