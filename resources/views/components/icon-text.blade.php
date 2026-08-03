@props(['icon'])

<span {{ $attributes->class(['meta']) }}>
    <x-ui-icon size="sm" :name="$icon" />
    <span>{{ $slot }}</span>
</span>
