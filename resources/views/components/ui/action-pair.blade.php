@props([
    'primaryLabel',
    'primaryIcon',
    'secondaryLabel',
    'secondaryIcon',
    'size' => 'profile',
    'primaryEndpoint' => null,
    'primaryPayload' => [],
    'primaryHref' => null,
    'primaryActive' => false,
    'primaryActiveLabel' => null,
    'primaryActiveIcon' => null,
    'secondaryEndpoint' => null,
    'secondaryPayload' => [],
    'secondaryHref' => null,
])

<x-ui.action-group {{ $attributes }}>
    <x-ui.action-control
        :label="$secondaryLabel"
        :icon="$secondaryIcon"
        :endpoint="$secondaryEndpoint"
        :payload="$secondaryPayload"
        :href="$secondaryHref"
        variant="paper"
        :size="$size"
    />
    <x-ui.action-control
        :label="$primaryLabel"
        :icon="$primaryIcon"
        :endpoint="$primaryEndpoint"
        :payload="$primaryPayload"
        :href="$primaryHref"
        :active="$primaryActive"
        :active-label="$primaryActiveLabel"
        :active-icon="$primaryActiveIcon"
        :pressed="$primaryEndpoint ? $primaryActive : null"
        variant="primary"
        :size="$size"
    />
</x-ui.action-group>
