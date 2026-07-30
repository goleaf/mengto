@props(['entries'])

<div class="care-timeline">
    @forelse ($entries as $entry)
        <article class="care-timeline__item">
            <span class="care-timeline__icon {{ $entry['is_unusual'] ? 'care-timeline__icon--attention' : '' }}">
                <x-dynamic-component :component="'lucide-'.$entry['icon']" class="size-4" aria-hidden="true" />
            </span>
            <div class="care-timeline__content">
                <div class="flex min-w-0 flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold">{{ $entry['title'] }}</h3>
                            <x-status-badge :label="$entry['status_label']" :icon="$entry['status'] === 'completed' ? 'circle-check' : 'circle-alert'" :tone="$entry['status_tone']" />
                            @if ($entry['is_unusual'])
                                <x-status-badge label="Unusual observation" icon="triangle-alert" tone="warning" />
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-paw-muted">
                            {{ $entry['type_label'] }} · {{ $entry['started_at'] }} · {{ $entry['author_name'] }}
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-paw-muted">{{ $entry['source'] }}</span>
                </div>

                <div class="care-timeline__facts">
                    @forelse ($entry['facts'] as $fact)
                        <span><strong>{{ $fact['label'] }}:</strong> {{ $fact['value'] }}</span>
                    @empty
                        <span class="sr-only">No measured details.</span>
                    @endforelse
                </div>

                @if ($entry['measurements'])
                    <dl class="care-inline-details">
                        @forelse ($entry['measurements'] as $detail)
                            <div><dt>{{ $detail['label'] }}</dt><dd>{{ $detail['value'] }}</dd></div>
                        @empty
                            <div><dt>Measurements</dt><dd>Not recorded</dd></div>
                        @endforelse
                    </dl>
                @endif

                @if ($entry['context'])
                    <dl class="care-inline-details">
                        @forelse ($entry['context'] as $detail)
                            <div><dt>{{ $detail['label'] }}</dt><dd>{{ $detail['value'] }}</dd></div>
                        @empty
                            <div><dt>Context</dt><dd>Not recorded</dd></div>
                        @endforelse
                    </dl>
                @endif

                @if ($entry['notes'])
                    <p class="mt-3 text-sm leading-6 text-paw-muted">{{ $entry['notes'] }}</p>
                @endif

                @if ($entry['media'])
                    <div class="care-media-list">
                        @forelse ($entry['media'] as $media)
                            <a href="{{ $media['download_url'] }}" class="care-media-link">
                                <x-lucide-paperclip class="size-4" aria-hidden="true" />
                                <span>{{ $media['alt_text'] }}</span>
                                <small>{{ $media['sensitivity_label'] }}</small>
                            </a>
                        @empty
                            <span class="text-sm text-paw-muted">No private media.</span>
                        @endforelse
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="care-empty">
            <x-lucide-notebook-tabs class="size-7" aria-hidden="true" />
            <p>No care actions have been recorded for this period.</p>
        </div>
    @endforelse
</div>
