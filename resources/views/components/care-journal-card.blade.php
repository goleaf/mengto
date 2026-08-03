@props(['journal'])

<article {{ $attributes->class(['care-journal-card']) }}>
    <a href="{{ $journal['show_url'] }}" class="care-journal-card__media" aria-label="{{ __('presentation.open_care_journal', ['pet' => $journal['pet_name']]) }}">
        @if ($journal['image_url'])
            <img src="{{ $journal['image_url'] }}" alt="{{ $journal['pet_name'] }}">
        @else
            <x-ui-icon name="paw-print" size="2xl" />
        @endif
    </a>
    <div class="care-journal-card__body">
        <div class="flex min-w-0 items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase text-paw-leaf">{{ $journal['species'] }}</p>
                <h2 class="mt-1 text-xl font-bold">
                    <a href="{{ $journal['show_url'] }}">{{ $journal['pet_name'] }}</a>
                </h2>
                <p class="mt-1 text-sm text-paw-muted">{{ $journal['breed'] }}</p>
            </div>
            <x-status-badge label="{{ __('ui.private_c63eb6720c') }}" icon="lock-keyhole" tone="surface" />
        </div>

        <dl class="care-journal-card__stats">
            <div>
                <dt>{{ __('ui.today_2b065c7c9c') }}</dt>
                <dd>{{ __('presentation.recorded_count', ['count' => $journal['today_entries_count']]) }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.open_tasks_87cfa1a507') }}</dt>
                <dd>{{ $journal['open_tasks_count'] }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.last_feeding_5a15632589') }}</dt>
                <dd>{{ $journal['last_feeding'] }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.last_walk_2648a88ee0') }}</dt>
                <dd>{{ $journal['last_walk'] }}</dd>
            </div>
        </dl>

        @if ($journal['overdue_tasks_count'] || $journal['unusual_entries_count'])
            <div class="care-journal-card__attention">
                <x-ui-icon name="triangle-alert" size="sm" />
                <span>
                    {{ __('presentation.care_week_status', ['overdue' => $journal['overdue_tasks_count'], 'unusual' => $journal['unusual_entries_count']]) }}
                </span>
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <x-action-control :href="$journal['show_url']" label="{{ __('ui.open_journal_1844ec9cd3') }}" icon="notebook-tabs" variant="primary" />
            <x-action-control :href="$journal['manage_url']" label="{{ __('ui.plan_care_b0602a259f') }}" icon="list-checks" />
        </div>
    </div>
</article>
