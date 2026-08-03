@props(['entries'])

<div class="care-timeline">
    @forelse ($entries as $entry)
        <article class="care-timeline__item">
            <span class="care-timeline__icon {{ $entry['is_unusual'] ? 'care-timeline__icon--attention' : '' }}">
                <x-ui-icon size="sm" :name="$entry['icon']" />
            </span>
            <div class="care-timeline__content">
                <div class="flex min-w-0 flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold">{{ $entry['title'] }}</h3>
                            <x-status-badge :label="$entry['status_label']" :icon="$entry['status'] === 'completed' ? 'circle-check' : 'circle-alert'" :tone="$entry['status_tone']" />
                            @if ($entry['sync_status'] === 'synchronized')
                                <x-status-badge :label="$entry['sync_label']" icon="cloud-check" tone="surface" />
                            @endif
                            @if ($entry['is_unusual'])
                                <x-status-badge label="{{ __('ui.unusual_observation_a958dc6268') }}" icon="triangle-alert" tone="warning" />
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
                        <span class="sr-only">{{ __('ui.no_measured_details_379b08d5ca') }}</span>
                    @endforelse
                </div>

                @if ($entry['measurements'])
                    <dl class="care-inline-details">
                        @forelse ($entry['measurements'] as $detail)
                            <div><dt>{{ $detail['label'] }}</dt><dd>{{ $detail['value'] }}</dd></div>
                        @empty
                            <div><dt>{{ __('ui.measurements_3c75780356') }}</dt><dd>{{ __('ui.not_recorded_b37c7879f6') }}</dd></div>
                        @endforelse
                    </dl>
                @endif

                @if ($entry['context'])
                    <dl class="care-inline-details">
                        @forelse ($entry['context'] as $detail)
                            <div><dt>{{ $detail['label'] }}</dt><dd>{{ $detail['value'] }}</dd></div>
                        @empty
                            <div><dt>{{ __('ui.context_a6e600a10f') }}</dt><dd>{{ __('ui.not_recorded_b37c7879f6') }}</dd></div>
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
                                <x-ui-icon name="paperclip" size="sm" />
                                <span>{{ $media['alt_text'] }}</span>
                                <small>{{ $media['sensitivity_label'] }}</small>
                            </a>
                        @empty
                            <span class="text-sm text-paw-muted">{{ __('ui.no_private_media_f0c6a2550f') }}</span>
                        @endforelse
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="care-empty">
            <x-ui-icon name="notebook-tabs" size="xl" />
            <p>{{ __('ui.no_care_actions_have_been_recorded_for_this_4e87ea5361') }}</p>
        </div>
    @endforelse
</div>
