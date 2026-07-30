@props(['align' => 'end'])

<div {{ $attributes->class([
    'grid grid-cols-2 gap-2 sm:flex',
    'sm:ml-auto' => $align === 'end',
]) }}>
    {{ $slot }}
</div>
