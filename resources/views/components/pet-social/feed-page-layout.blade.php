<div {{ $attributes->class(['grid gap-5 lg:grid-cols-[18rem_minmax(0,1fr)_20rem]']) }}>
    <div class="order-1 min-w-0 lg:order-2">
        {{ $feed }}
    </div>

    <aside class="order-2 lg:order-1">
        {{ $profile }}
    </aside>

    <aside class="order-3 grid content-start gap-4">
        {{ $sidebar }}
    </aside>
</div>
