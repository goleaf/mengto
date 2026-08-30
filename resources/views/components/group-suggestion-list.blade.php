@props(['groups'])

<x-sidebar-section title="{{ __('ui.groups_for_you') }}" section="groups" :href="route('groups.index')">
    <x-sidebar-list>
        @forelse ($groups as $group)
            <x-sidebar-list-item>
                <h3 class="break-words text-sm font-semibold text-paw-ink">
                    <x-optional-link
                        :route-name="$group['detail_route'] ?? null"
                        :route-parameters="$group['detail_parameters'] ?? []"
                    >
                        {{ $group['name'] }}
                    </x-optional-link>
                </h3>
                <p class="mt-1 text-sm text-paw-muted">{{ $group['topic'] }}</p>

                <div class="mt-3 flex items-center justify-between gap-3">
                    <x-icon-text icon="users">{{ $group['members'] }}</x-icon-text>
                    <x-action-control
                        label="{{ __('ui.join') }}"
                        active-label="{{ __('ui.joined') }}"
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
            </x-sidebar-list-item>
        @empty
            <p role="listitem" class="sidebar-list__empty">{{ __('ui.no_group_suggestions_yet') }}</p>
        @endforelse
    </x-sidebar-list>
</x-sidebar-section>
