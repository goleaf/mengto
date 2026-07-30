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
                    <x-status-badge label="{{ __('ui.qualification_verified_bfd453f9ac') }}" icon="badge-check" tone="success" />
                @else
                    <x-status-badge label="{{ $expert['verification'] }}" icon="circle-help" />
                @endif
            </div>
            <p class="mt-1 text-sm font-semibold text-paw-leaf">{{ $expert['type'] }}</p>
            <p class="mt-1 text-sm text-paw-muted">{{ $expert['city'] }}</p>
        </div>
    </header>

    <div>
        <p class="font-semibold">{{ $expert['headline'] }}</p>
        <div class="mt-3 flex flex-wrap gap-2" aria-label="{{ __('ui.specializations_b2561b50e1') }}">
            @forelse (array_slice($expert['specializations'], 0, 3) as $specialization)
                <span class="rounded border border-paw-line bg-paw-paper px-2 py-1 text-xs font-semibold">{{ $specialization }}</span>
            @empty
                <span class="text-sm text-paw-muted">{{ __('ui.scope_details_pending_4f7b588ab8') }}</span>
            @endforelse
        </div>
    </div>

    @if ($expert['reasons'] !== [])
        <ul class="grid gap-1 text-sm text-paw-muted" aria-label="{{ __('ui.why_this_profile_matches_8f43aa2b22') }}">
            @forelse ($expert['reasons'] as $reason)
                <li class="flex items-center gap-2">
                    <x-lucide-check class="size-4 text-paw-leaf" aria-hidden="true" />
                    <span>{{ $reason }}</span>
                </li>
            @empty
                <li>{{ __('ui.matches_the_current_directory_f43672c613') }}</li>
            @endforelse
        </ul>
    @endif

    <dl class="grid grid-cols-2 gap-3 border-y border-paw-line py-3 text-sm">
        <div>
            <dt class="text-paw-muted">{{ __('ui.next_time_651c943284') }}</dt>
            <dd class="mt-1 font-semibold">{{ $expert['next_available'] ?? __('ui.by_request_6abaa6de2b') }}</dd>
        </div>
        <div>
            <dt class="text-paw-muted">{{ __('ui.price_93c91c851e') }}</dt>
            <dd class="mt-1 font-semibold">
                {{ $expert['price_from'] !== null ? __('ui.from_2181976934').' '.$expert['currency'].' '.$expert['price_from'] : __('ui.ask_for_price_98fd0280eb') }}
            </dd>
        </div>
        <div>
            <dt class="text-paw-muted">{{ __('ui.client_rating_9bc6657b50') }}</dt>
            <dd class="mt-1 font-semibold">
                {{ $expert['review_count'] > 0 ? $expert['rating'].' / 5' : __('ui.new_profile_fcf4f3f4d5') }}
            </dd>
        </div>
        <div>
            <dt class="text-paw-muted">{{ __('ui.verified_reviews_dd3744117b') }}</dt>
            <dd class="mt-1 font-semibold">{{ $expert['verified_review_count'] }}</dd>
        </div>
    </dl>

    <footer class="mt-auto flex flex-wrap gap-2">
        <x-action-control
            label="{{ __('ui.view_profile_d4788f256f') }}"
            icon="arrow-right"
            variant="primary"
            :href="route('experts.show', $expert['slug'])"
        />
        @if ($expert['accepts_new_clients'])
            <x-action-control
                label="{{ __('ui.book_909cb81127') }}"
                icon="calendar-plus"
                :href="route('experts.bookings.create', $expert['slug'])"
            />
        @endif
    </footer>
</article>
