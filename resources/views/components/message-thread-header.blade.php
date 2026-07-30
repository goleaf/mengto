@props(['contact'])

<header class="grid grid-cols-[2.75rem_minmax(0,1fr)] items-center gap-3 border-b border-paw-line p-4 sm:flex sm:p-5">
    <x-avatar
        :src="$contact['avatar']"
        :alt="$contact['avatar_alt']"
        size="thread"
    />

    <div class="min-w-0">
        <h2 class="text-base font-semibold text-paw-ink">{{ $contact['name'] }}</h2>
        <p class="mt-0.5 text-xs text-paw-muted">{{ $contact['detail'] }}</p>
        <p class="mt-1 text-xs font-semibold text-paw-leaf">{{ $contact['response_note'] }}</p>
    </div>

    <x-action-group class="col-span-2 sm:ml-auto sm:shrink-0">
        <x-action-control
            label="{{ __('ui.call_d6e645b7d2') }}"
            icon="phone"
            variant="paper"
            size="toolbar"
            :endpoint="route('actions.perform')"
            :payload="['action' => 'call', 'target' => $contact['key'], 'label' => $contact['name']]"
            :active="$contact['call_requested']"
            active-label="{{ __('ui.cancel_request_5619668359') }}"
            active-icon="phone-off"
            :pressed="$contact['call_requested']"
        />
        <x-action-control
            label="{{ __('ui.info_170322a32f') }}"
            icon="info"
            variant="paper"
            size="toolbar"
            :endpoint="route('actions.perform')"
            :payload="['action' => 'show-info', 'target' => $contact['key'], 'label' => $contact['name']]"
        />
    </x-action-group>
</header>
