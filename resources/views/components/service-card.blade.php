@props(['service', 'expertSlug' => null])

<article class="grid gap-3 rounded-md border border-paw-line bg-white p-4">
    <header class="flex items-start justify-between gap-4">
        <div>
            <h3 class="font-bold">{{ $service['name'] }}</h3>
            <p class="mt-1 text-sm text-paw-muted">{{ $service['format'] }} · {{ $service['duration'] }}</p>
        </div>
        <p class="whitespace-nowrap font-bold">
            {{ $service['price'] !== null ? $service['currency'].' '.$service['price'] : 'Ask' }}
        </p>
    </header>

    <p class="text-sm leading-6">{{ $service['description'] }}</p>

    @if ($service['includes'] !== [])
        <ul class="grid gap-1 text-sm text-paw-muted">
            @forelse ($service['includes'] as $item)
                <li class="flex gap-2"><x-lucide-check class="mt-0.5 size-4 text-paw-leaf" aria-hidden="true" /> {{ $item }}</li>
            @empty
                <li>Details provided during booking.</li>
            @endforelse
        </ul>
    @endif

    @if ($expertSlug)
        <x-action-control
            label="Choose service"
            icon="calendar-plus"
            variant="primary"
            :href="route('experts.bookings.create', ['expertProfile' => $expertSlug, 'service' => $service['id']])"
        />
    @endif
</article>
