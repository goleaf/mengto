@props(['record'])

<article {{ $attributes->class(['medical-record-card']) }}>
    <a href="{{ route('medical-records.show', $record['slug']) }}" class="medical-record-card__media">
        @if ($record['image_url'])
            <img src="{{ $record['image_url'] }}" alt="{{ $record['pet_name'] }} health record" loading="lazy">
        @else
            <x-lucide-heart-pulse class="size-10" aria-hidden="true" />
        @endif
    </a>

    <div class="medical-record-card__body">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase text-paw-leaf">Private health record</p>
                <h2 class="mt-1 text-xl font-bold">
                    <a href="{{ route('medical-records.show', $record['slug']) }}">{{ $record['pet_name'] }}</a>
                </h2>
                <p class="mt-1 text-sm text-paw-muted">{{ $record['species'] }} · {{ $record['breed'] }}</p>
            </div>
            <x-status-badge label="Private" icon="lock-keyhole" tone="surface" />
        </div>

        <dl class="medical-record-card__stats">
            <div>
                <dt>Weight</dt>
                <dd>{{ $record['current_weight'] }}</dd>
            </div>
            <div>
                <dt>Medications</dt>
                <dd>{{ $record['active_medications_count'] }}</dd>
            </div>
            <div>
                <dt>Tasks</dt>
                <dd>{{ $record['upcoming_reminders_count'] }}</dd>
            </div>
        </dl>

        <div class="mt-4 flex items-center justify-between gap-3 border-t border-paw-line pt-4 text-sm">
            <span class="text-paw-muted">Last visit: {{ $record['last_visit'] }}</span>
            <a href="{{ route('medical-records.show', $record['slug']) }}" class="inline-flex items-center gap-1 font-bold text-paw-leaf">
                Open
                <x-lucide-arrow-right class="size-4" aria-hidden="true" />
            </a>
        </div>
    </div>
</article>
