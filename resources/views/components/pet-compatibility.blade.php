@props([
    'compatibility',
])

<section class="compatibility" aria-label="{{ __('ui.owner_reviewed_compatibility_notes') }}">
    <div class="compatibility__heading">
        <x-ui-icon name="sparkles" size="sm" />
        <div>
            <h4>{{ __('ui.shared_routines') }}</h4>
            <p>{{ $compatibility['reason'] }}</p>
        </div>
    </div>

    @if ($compatibility['shared'] !== [])
        <ul class="compatibility__list compatibility__list--shared">
            @forelse ($compatibility['shared'] as $point)
                <li>
                    <x-ui-icon name="check" size="sm" />
                    <span>{{ $point }}</span>
                </li>
            @empty
            @endforelse
        </ul>
    @endif

    @if ($compatibility['cautions'] !== [])
        <div class="compatibility__cautions">
            <p>
                <x-ui-icon name="message-circle-warning" size="sm" />
                {{ __('ui.discuss_before_meeting') }}
            </p>
            <ul class="compatibility__list">
                @forelse ($compatibility['cautions'] as $caution)
                    <li>{{ $caution }}</li>
                @empty
                @endforelse
            </ul>
        </div>
    @endif
</section>
