@props([
    'compatibility',
])

<section class="compatibility" aria-label="Owner-reviewed compatibility notes">
    <div class="compatibility__heading">
        <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
        <div>
            <h4>Shared routines</h4>
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
                Discuss before meeting
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
