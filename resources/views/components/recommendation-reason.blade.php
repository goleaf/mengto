@if ($resolvedReason)
    <div {{ $attributes->class(['recommendation-reason']) }}>
        <div class="recommendation-reason__copy">
            <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
            <div>
                <p class="recommendation-reason__label">{{ __('ui.why_this_profile_11657790a4') }}</p>
                <p>{{ $resolvedReason }}</p>
            </div>
        </div>

        @if ($resolvedSignals !== [])
            <div class="recommendation-reason__signals" aria-label="{{ __('ui.recommendation_signals_a853668995') }}">
                @forelse ($resolvedSignals as $signal)
                    <span>{{ $signal }}</span>
                @empty
                @endforelse
            </div>
        @endif
    </div>
@endif
