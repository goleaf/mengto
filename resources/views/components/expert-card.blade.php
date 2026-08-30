@props(['expert'])

<article data-expert-card class="grid min-h-full gap-4 rounded-md border border-paw-line bg-white p-5 shadow-sm">
    <header class="flex items-start gap-4">
        <x-linked-media
            :href="$expert['media_target']['url']"
            :label="$expert['media_target']['label']"
            variant="avatar"
            class="shrink-0"
        >
            @if ($expert['avatar_url'])
                <img
                    src="{{ $expert['avatar_url'] }}"
                    alt=""
                    width="64"
                    height="64"
                    class="size-16 rounded-full object-cover"
                    loading="lazy"
                >
            @else
                <span class="grid size-16 place-items-center rounded-full bg-paw-mint text-lg font-bold text-paw-leaf" aria-hidden="true">
                    {{ $expert['initials'] }}
                </span>
            @endif
        </x-linked-media>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <x-card-heading
                    :title="$expert['name']"
                    :href="$expert['profile_url']"
                    :level="2"
                    spacing="none"
                />
                @if ($expert['qualification_verified'])
                    <x-status-badge data-expert-card-badge label="{{ __('ui.qualification_verified') }}" icon="badge-check" tone="success" />
                @else
                    <x-status-badge data-expert-card-badge label="{{ $expert['verification'] }}" icon="circle-help" />
                @endif
            </div>
            <p data-expert-card-type class="mt-1 text-sm font-semibold text-paw-leaf">{{ $expert['type'] }}</p>
            <p class="mt-1 text-sm text-paw-muted">{{ $expert['city'] }}</p>
        </div>
    </header>

    <div>
        <p class="font-semibold">{{ $expert['headline'] }}</p>
        <div data-expert-card-specializations class="mt-3 flex flex-wrap gap-2" aria-label="{{ __('ui.specializations') }}">
            @forelse (array_slice($expert['specializations'], 0, 3) as $specialization)
                <span class="rounded border border-paw-line bg-paw-paper px-2 py-1 text-xs font-semibold">{{ $specialization }}</span>
            @empty
                <span class="text-sm text-paw-muted">{{ __('ui.scope_details_pending') }}</span>
            @endforelse
        </div>
    </div>

    @if ($expert['reasons'] !== [])
        <ul class="grid gap-1 text-sm text-paw-muted" aria-label="{{ __('ui.why_this_profile_matches') }}">
            @forelse ($expert['reasons'] as $reason)
                <li class="flex items-center gap-2">
                    <x-ui-icon name="check" size="sm" class="text-paw-leaf" />
                    <span>{{ $reason }}</span>
                </li>
            @empty
                <li>{{ __('ui.matches_the_current_directory') }}</li>
            @endforelse
        </ul>
    @endif

    <dl data-expert-card-facts class="grid grid-cols-2 gap-3 border-y border-paw-line py-3 text-sm">
        <div>
            <dt class="text-paw-muted">{{ __('ui.next_time') }}</dt>
            <dd class="mt-1 font-semibold">{{ $expert['next_available'] ?? __('ui.by_request') }}</dd>
        </div>
        <div>
            <dt class="text-paw-muted">{{ __('ui.price') }}</dt>
            <dd class="mt-1 font-semibold">
                {{ $expert['price_from'] !== null ? __('ui.from').' '.$expert['currency'].' '.$expert['price_from'] : __('ui.ask_for_price') }}
            </dd>
        </div>
        <div>
            <dt class="text-paw-muted">{{ __('ui.client_rating') }}</dt>
            <dd class="mt-1 font-semibold">
                {{ $expert['review_count'] > 0 ? $expert['rating'].' / 5' : __('ui.new_profile') }}
            </dd>
        </div>
        <div>
            <dt class="text-paw-muted">{{ __('ui.verified_reviews') }}</dt>
            <dd class="mt-1 font-semibold">{{ $expert['verified_review_count'] }}</dd>
        </div>
    </dl>

    <footer class="mt-auto flex flex-wrap gap-2">
        <x-action-control
            data-expert-card-view
            label="{{ __('ui.view_profile') }}"
            icon="arrow-right"
            variant="primary"
            :href="$expert['profile_url']"
        />
        @if ($expert['accepts_new_clients'])
            <x-action-control
                data-expert-card-book
                label="{{ __('ui.book') }}"
                icon="calendar-plus"
                :href="route('experts.bookings.create', $expert['slug'])"
            />
        @endif
    </footer>
</article>
