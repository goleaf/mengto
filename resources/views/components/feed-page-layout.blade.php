<div {{ $attributes->class(['grid gap-5 xl:grid-cols-[18rem_minmax(0,1fr)_20rem]']) }}>
    <div class="order-1 min-w-0 xl:order-2">
        {{ $feed }}
    </div>

    <aside class="order-2 xl:order-1">
        {{ $profile }}
    </aside>

    <aside class="order-3 grid content-start gap-4">
        {{ $sidebar }}
    </aside>
</div>
