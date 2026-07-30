@props(['expert'])

<article class="grid min-h-full gap-4 rounded-md border border-paw-line bg-white p-5 shadow-sm">
    <header class="flex items-start gap-4">
        @if ($expert['avatar_url'])
            <img
                src="{{ $expert['avatar_url'] }}"
                alt=""
                class="size-16 shrink-0 rounded-full object-cover"
                loading="lazy"
            >
        @else
            <span class="grid size-16 shrink-0 place-items-center rounded-full bg-paw-mint text-lg font-bold text-paw-leaf" aria-hidden="true">
                {{ $expert['initials'] }}
            </span>
        @endif

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-bold leading-tight">
                    <a href="{{ route('experts.show', $expert['slug']) }}" class="hover:text-paw-leaf focus:outline-none focus:ring-2 focus:ring-paw-leaf">
                        {{ $expert['name'] }}
                    </a>
                </h2>
                @if ($expert['qualification_verified'])
                    <x-ui.status-badge label="Qualification verified" icon="badge-check" tone="success" />
                @else
                    <x-ui.status-badge label="{{ $expert['verification'] }}" icon="circle-help" />
                @endif
            </div>
            <p class="mt-1 text-sm font-semibold text-paw-leaf">{{ $expert['type'] }}</p>
            <p class="mt-1 text-sm text-paw-muted">{{ $expert['city'] }}</p>
        </div>
    </header>

    <div>
        <p class="font-semibold">{{ $expert['headline'] }}</p>
        <div class="mt-3 flex flex-wrap gap-2" aria-label="Specializations">
            @forelse (array_slice($expert['specializations'], 0, 3) as $specialization)
                <span class="rounded border border-paw-line bg-paw-paper px-2 py-1 text-xs font-semibold">{{ $specialization }}</span>
            @empty
                <span class="text-sm text-paw-muted">Scope details pending.</span>
            @endforelse
        </div>
    </div>

    @if ($expert['reasons'] !== [])
        <ul class="grid gap-1 text-sm text-paw-muted" aria-label="Why this profile matches">
            @forelse ($expert['reasons'] as $reason)
                <li class="flex items-center gap-2">
                    <x-lucide-check class="size-4 text-paw-leaf" aria-hidden="true" />
                    <span>{{ $reason }}</span>
                </li>
            @empty
                <li>Matches the current directory.</li>
            @endforelse
        </ul>
    @endif

    <dl class="grid grid-cols-2 gap-3 border-y border-paw-line py-3 text-sm">
        <div>
            <dt class="text-paw-muted">Next time</dt>
            <dd class="mt-1 font-semibold">{{ $expert['next_available'] ?? 'By request' }}</dd>
        </div>
        <div>
            <dt class="text-paw-muted">Price</dt>
            <dd class="mt-1 font-semibold">
                {{ $expert['price_from'] !== null ? 'From '.$expert['currency'].' '.$expert['price_from'] : 'Ask for price' }}
            </dd>
        </div>
        <div>
            <dt class="text-paw-muted">Client rating</dt>
            <dd class="mt-1 font-semibold">
                {{ $expert['review_count'] > 0 ? $expert['rating'].' / 5' : 'New profile' }}
            </dd>
        </div>
        <div>
            <dt class="text-paw-muted">Verified reviews</dt>
            <dd class="mt-1 font-semibold">{{ $expert['verified_review_count'] }}</dd>
        </div>
    </dl>

    <footer class="mt-auto flex flex-wrap gap-2">
        <x-ui.action-control
            label="View profile"
            icon="arrow-right"
            variant="primary"
            :href="route('experts.show', $expert['slug'])"
        />
        @if ($expert['accepts_new_clients'])
            <x-ui.action-control
                label="Book"
                icon="calendar-plus"
                :href="route('experts.bookings.create', $expert['slug'])"
            />
        @endif
    </footer>
</article>
