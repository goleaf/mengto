@props(['variant' => 'default'])

@php
    $layoutClass = match ($variant) {
        'compact' => 'gap-4 md:grid-cols-[minmax(0,1fr)_18rem] lg:grid-cols-[minmax(0,1fr)_20rem]',
        default => 'gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]',
    };

    $sidebarClass = $variant === 'default'
        ? 'md:grid-cols-3 lg:grid-cols-1'
        : null;
@endphp

<div {{ $attributes->class(['grid items-start', $layoutClass]) }}>
    <div class="min-w-0">
        {{ $main }}
    </div>

    <aside @class(['grid min-w-0 content-start gap-4', $sidebarClass])>
        {{ $sidebar }}
    </aside>
</div>
