@props([
    'days' => [],
    'canManage' => false,
])

<div {{ $attributes->class('event-schedule') }}>
    @forelse ($days as $day)
        <section class="border-t border-paw-line py-5 first:border-t-0" aria-labelledby="event-schedule-day-{{ $day['key'] }}">
            <div class="grid gap-4 md:grid-cols-[10rem_minmax(0,1fr)]">
                <h3 id="event-schedule-day-{{ $day['key'] }}" class="text-base">
                    <time datetime="{{ $day['date_iso'] }}">{{ $day['date'] }}</time>
                </h3>

                <ol class="grid gap-0">
                    @forelse ($day['sessions'] as $session)
                        <li class="border-b border-paw-line py-4 first:pt-0 last:border-b-0 last:pb-0" wire:key="event-session-{{ $session['id'] }}">
                            <article class="grid gap-3 sm:grid-cols-[7rem_minmax(0,1fr)_auto] sm:items-start">
                                <p class="font-semibold tabular-nums">
                                    <time datetime="{{ $session['starts_at_iso'] }}">{{ $session['starts_at'] }}</time>
                                    <span aria-hidden="true">–</span>
                                    <time datetime="{{ $session['ends_at_iso'] }}">{{ $session['ends_at'] }}</time>
                                </p>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-base">{{ $session['title'] }}</h4>
                                        <x-status-badge :label="$session['status']" icon="calendar-clock" />
                                    </div>
                                    <p class="text-sm">{{ $session['type'] }}</p>
                                    @if ($session['summary'])
                                        <p class="mt-2 whitespace-pre-line">{{ $session['summary'] }}</p>
                                    @endif
                                    <dl class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                        @if ($session['track'])
                                            <div class="inline-flex gap-1">
                                                <dt>{{ __('forum_events.schedule.track') }}:</dt>
                                                <dd>{{ $session['track'] }}</dd>
                                            </div>
                                        @endif
                                        @if ($session['room'])
                                            <div class="inline-flex gap-1">
                                                <dt>{{ __('forum_events.schedule.room') }}:</dt>
                                                <dd>{{ $session['room'] }}</dd>
                                            </div>
                                        @endif
                                        @if ($session['capacity'])
                                            <div class="inline-flex gap-1">
                                                <dt>{{ __('forum_events.fields.capacity') }}:</dt>
                                                <dd>{{ $session['capacity'] }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                    @if ($session['staff'] !== [])
                                        <ul class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-sm" aria-label="{{ __('forum_events.schedule.session_team') }}">
                                            @forelse ($session['staff'] as $staff)
                                                <li>{{ $staff['name'] }} · {{ $staff['role'] }}</li>
                                            @empty
                                            @endforelse
                                        </ul>
                                    @endif
                                    @if ($session['room_directions'])
                                        <p class="mt-2 text-sm">{{ $session['room_directions'] }}</p>
                                    @endif
                                </div>

                                @if ($canManage)
                                    <button
                                        type="button"
                                        class="forum-button min-h-11"
                                        wire:click="editSession({{ $session['id'] }})"
                                        wire:loading.attr="disabled"
                                        wire:target="editSession"
                                    >
                                        <x-lucide-pencil aria-hidden="true" />
                                        <span>{{ __('forum_events.actions.edit_session') }}</span>
                                    </button>
                                @endif
                            </article>
                        </li>
                    @empty
                    @endforelse
                </ol>
            </div>
        </section>
    @empty
        <x-empty-state
            icon="calendar-range"
            title="{{ __('forum_events.empty.schedule_title') }}"
            description="{{ __('forum_events.empty.schedule_description') }}"
        />
    @endforelse
</div>
