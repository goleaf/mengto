@props(['journal'])

<article {{ $attributes->class(['care-journal-card']) }}>
    <a href="{{ $journal['show_url'] }}" class="care-journal-card__media" aria-label="Open {{ $journal['pet_name'] }} care journal">
        @if ($journal['image_url'])
            <img src="{{ $journal['image_url'] }}" alt="{{ $journal['pet_name'] }}">
        @else
            <x-lucide-paw-print class="size-8" aria-hidden="true" />
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
            <x-status-badge label="Private" icon="lock-keyhole" tone="surface" />
        </div>

        <dl class="care-journal-card__stats">
            <div>
                <dt>Today</dt>
                <dd>{{ $journal['today_entries_count'] }} recorded</dd>
            </div>
            <div>
                <dt>Open tasks</dt>
                <dd>{{ $journal['open_tasks_count'] }}</dd>
            </div>
            <div>
                <dt>Last feeding</dt>
                <dd>{{ $journal['last_feeding'] }}</dd>
            </div>
            <div>
                <dt>Last walk</dt>
                <dd>{{ $journal['last_walk'] }}</dd>
            </div>
        </dl>

        @if ($journal['overdue_tasks_count'] || $journal['unusual_entries_count'])
            <div class="care-journal-card__attention">
                <x-lucide-triangle-alert class="size-4" aria-hidden="true" />
                <span>
                    {{ $journal['overdue_tasks_count'] }} overdue ·
                    {{ $journal['unusual_entries_count'] }} unusual this week
                </span>
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <x-action-control :href="$journal['show_url']" label="Open journal" icon="notebook-tabs" variant="primary" />
            <x-action-control :href="$journal['manage_url']" label="Plan care" icon="list-checks" />
        </div>
    </div>
</article>
