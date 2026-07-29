@props(['gap' => 'page'])

@php
    $gapClass = match ($gap) {
        'section' => 'gap-4',
        'compact' => 'gap-3',
        default => 'gap-5',
    };
@endphp

<div {{ $attributes->class(['grid min-w-0', $gapClass]) }}>
    {{ $slot }}
</div>
