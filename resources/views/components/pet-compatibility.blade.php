@props([
    'compatibility',
])

<section class="compatibility" aria-label="{{ __('ui.owner_reviewed_compatibility_notes_f93c277a89') }}">
    <div class="compatibility__heading">
        <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
        <div>
            <h4>{{ __('ui.shared_routines_36e841cb6c') }}</h4>
            <p>{{ $compatibility['reason'] }}</p>
        </div>
    </div>

    @if ($compatibility['shared'] !== [])
        <ul class="compatibility__list compatibility__list--shared">
            @forelse ($compatibility['shared'] as $point)
                <li>
                    <x-lucide-check class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $point }}</span>
                </li>
            @empty
            @endforelse
        </ul>
    @endif

    @if ($compatibility['cautions'] !== [])
        <div class="compatibility__cautions">
            <p>
                <x-lucide-message-circle-warning class="icon icon--sm" aria-hidden="true" />
                {{ __('ui.discuss_before_meeting_e113b4e8b3') }}
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
