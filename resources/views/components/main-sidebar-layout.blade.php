<div {{ $attributes->class(['grid items-start', $layoutClass]) }}>
    <div class="min-w-0">
        {{ $main }}
    </div>

    <aside @class(['grid min-w-0 content-start gap-4', $sidebarClass])>
        {{ $sidebar }}
    </aside>
</div>
