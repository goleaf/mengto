@props(['contact'])

<header class="grid grid-cols-[2.75rem_minmax(0,1fr)] items-center gap-3 border-b border-paw-line p-4 sm:flex sm:p-5">
    <x-pet-social.avatar
        :src="$contact['avatar']"
        :alt="$contact['avatar_alt']"
        size="thread"
    />

    <div class="min-w-0">
        <h2 class="truncate text-base font-semibold text-paw-ink">{{ $contact['name'] }}</h2>
        <p class="mt-0.5 truncate text-xs text-paw-muted">{{ $contact['detail'] }}</p>
        <p class="mt-1 truncate text-[0.65rem] font-semibold text-paw-leaf">{{ $contact['response_note'] }}</p>
    </div>

    <x-pet-social.action-group class="col-span-2 sm:ml-auto sm:shrink-0">
        <x-pet-social.static-action label="Call" icon="phone" variant="paper" size="toolbar" />
        <x-pet-social.static-action label="Info" icon="info" variant="paper" size="toolbar" />
    </x-pet-social.action-group>
</header>
