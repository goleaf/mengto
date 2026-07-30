@props(['events'])

<section {{ $attributes->class(['medical-section']) }} aria-labelledby="medical-timeline-title">
    <div class="medical-section__heading">
        <div>
            <p class="text-xs font-bold uppercase text-paw-leaf">History</p>
            <h2 id="medical-timeline-title" class="mt-1 text-xl font-bold">Medical timeline</h2>
        </div>
        <span class="text-sm font-semibold text-paw-muted">{{ count($events) }} entries</span>
    </div>

    <ol class="medical-timeline">
        @forelse ($events as $event)
            <li class="medical-timeline__item">
                <span class="medical-timeline__icon {{ $event['is_critical'] ? 'medical-timeline__icon--critical' : '' }}">
                    <x-dynamic-component :component="'lucide-'.$event['icon']" class="size-4" aria-hidden="true" />
                </span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-muted">{{ $event['type_label'] }} · {{ $event['occurred_at'] }}</p>
                            <h3 class="mt-1 font-bold">{{ $event['title'] }}</h3>
                        </div>
                        <x-status-badge :label="$event['verification']" icon="badge-check" :tone="$event['verification_tone']" />
                    </div>
                    @if ($event['summary'])
                        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ $event['summary'] }}</p>
                    @endif
                    <p class="mt-2 text-xs font-semibold text-paw-muted">{{ $event['source'] }} · {{ $event['source_name'] }}</p>
                    @if ($event['follow_up'])
                        <p class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-paw-leaf">
                            <x-lucide-calendar-clock class="size-3.5" aria-hidden="true" />
                            Follow-up {{ $event['follow_up'] }}
                        </p>
                    @endif
                </div>
            </li>
        @empty
            <li class="medical-empty">
                <x-lucide-notebook-pen class="size-7" aria-hidden="true" />
                <p>No medical events have been recorded.</p>
            </li>
        @endforelse
    </ol>
</section>
