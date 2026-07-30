@props(['collection', 'columns' => 2])

<x-layout.collection-section
    :section="'circle-'.$collection['key']"
    :eyebrow="$collection['eyebrow']"
    :title="$collection['title']"
    :title-id="'circle-'.$collection['key'].'-title'"
    :columns="$columns"
    {{ $attributes }}
>
    <x-slot:action>
        <x-ui.action-control
            :href="route($collection['action_route'])"
            :label="$collection['action_label']"
            :icon="$collection['action_icon']"
            variant="paper"
        />
    </x-slot:action>

    @forelse ($collection['items'] as $entry)
        <x-feature.circle-item :entry="$entry" :eager="$loop->first" />
    @empty
        <x-ui.empty-state
            :icon="$collection['empty_icon']"
            :title="$collection['empty_title']"
            :description="$collection['empty_description']"
            :href="route($collection['action_route'])"
            :action-label="$collection['action_label']"
            :action-icon="$collection['action_icon']"
            compact
            role="listitem"
            class="xl:col-span-2"
        />
    @endforelse
</x-layout.collection-section>
