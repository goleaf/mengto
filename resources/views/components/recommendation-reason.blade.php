@if ($resolvedReason)
    <div {{ $attributes->class(['recommendation-reason']) }}>
        <div class="recommendation-reason__copy">
            <x-ui-icon name="sparkles" size="sm" />
            <div>
                <p class="recommendation-reason__label">{{ __('ui.why_this_profile') }}</p>
                <p>{{ $resolvedReason }}</p>
            </div>
        </div>

        @if ($resolvedSignals !== [])
            <div class="recommendation-reason__signals" aria-label="{{ __('ui.recommendation_signals') }}">
                @forelse ($resolvedSignals as $signal)
                    <span>{{ $signal }}</span>
                @empty
                @endforelse
            </div>
        @endif
    </div>
@endif
