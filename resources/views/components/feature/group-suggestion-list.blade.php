@props(['groups'])

<x-layout.sidebar-section title="Groups for you" section="groups" :href="route('groups.index')">
    <x-object.sidebar-list>
        @forelse ($groups as $group)
            <x-object.sidebar-list-item>
                <h3 class="break-words text-sm font-semibold text-paw-ink">
                    <x-ui.optional-link
                        :route-name="$group['detail_route'] ?? null"
                        :route-parameters="$group['detail_parameters'] ?? []"
                    >
                        {{ $group['name'] }}
                    </x-ui.optional-link>
                </h3>
                <p class="mt-1 text-sm text-paw-muted">{{ $group['topic'] }}</p>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <x-ui.icon-text icon="users">{{ $group['members'] }}</x-ui.icon-text>
                    <x-ui.action-control
                        label="Join"
                        active-label="Joined"
                        icon="user-plus"
                        active-icon="check"
                        variant="quiet"
                        size="micro"
                        :active="$group['joined']"
                        :pressed="$group['joined']"
                        :endpoint="route('actions.perform')"
                        :payload="['action' => 'toggle-group', 'target' => $group['key'], 'label' => $group['name']]"
                    />
                </div>
            </x-object.sidebar-list-item>
        @empty
            <p role="listitem" class="sidebar-list__empty">No group suggestions yet.</p>
        @endforelse
    </x-object.sidebar-list>
</x-layout.sidebar-section>
