@props(['record'])

<article {{ $attributes->class(['medical-record-card']) }}>
    <a href="{{ route('medical-records.show', $record['slug']) }}" class="medical-record-card__media">
        @if ($record['image_url'])
            <x-responsive-image
                :src="$record['image_url']"
                :alt="__('presentation.medical_record_image_alt', ['pet' => $record['pet_name']])"
                :width="1200"
                :height="900"
                sizes="(min-width: 1024px) 360px, (min-width: 640px) 50vw, 100vw"
            />
        @else
            <x-ui-icon name="heart-pulse" size="3xl" />
        @endif
    </a>

    <div class="medical-record-card__body">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.private_health_record') }}</p>
                <h2 class="mt-1 text-xl font-bold">
                    <a href="{{ route('medical-records.show', $record['slug']) }}">{{ $record['pet_name'] }}</a>
                </h2>
                <p class="mt-1 text-sm text-paw-muted">{{ $record['species'] }} · {{ $record['breed'] }}</p>
            </div>
            <x-status-badge label="{{ __('ui.private') }}" icon="lock-keyhole" tone="surface" />
        </div>

        <dl class="medical-record-card__stats">
            <div>
                <dt>{{ __('ui.weight') }}</dt>
                <dd>{{ $record['current_weight'] }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.medications') }}</dt>
                <dd>{{ $record['active_medications_count'] }}</dd>
            </div>
            <div>
                <dt>{{ __('ui.tasks') }}</dt>
                <dd>{{ $record['upcoming_reminders_count'] }}</dd>
            </div>
        </dl>

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-paw-line pt-4 text-sm">
            <span class="text-paw-muted">{{ __('presentation.last_visit', ['date' => $record['last_visit']]) }}</span>
            <a href="{{ route('medical-records.show', $record['slug']) }}" class="inline-flex items-center gap-1 font-bold text-paw-leaf">
                {{ __('ui.open') }}
                <x-ui-icon name="arrow-right" size="sm" />
            </a>
        </div>
    </div>
</article>
