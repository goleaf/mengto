@props(['pet'])

<article role="listitem" class="border-b border-paw-line pb-3 last:border-b-0 last:pb-0">
    <div class="flex items-center justify-between gap-3">
        <h4 class="min-w-0 text-sm font-semibold text-paw-ink">
            <x-ui.optional-link
                :route-name="$pet['profile_route'] ?? null"
                :route-parameters="$pet['profile_parameters'] ?? []"
                data-profile-link
            >
                {{ $pet['name'] }}
            </x-ui.optional-link>
        </h4>

        <x-object.pet-badge :type="$pet['species'] ?? $pet['type']" tone="sun" />
    </div>

    <p class="mt-1 text-sm text-paw-muted">{{ $pet['breed'] }} · {{ $pet['age'] }}</p>
    <p class="mt-2 text-xs font-medium text-paw-coral">{{ $pet['status'] }}</p>
</article>
