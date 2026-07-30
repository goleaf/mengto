@props(['poll'])

<div class="post-poll" aria-label="Poll">
    @foreach ($poll['options'] as $option)
        <button type="button" class="post-poll__option" disabled>
            <span>{{ $option['label'] }}</span>
            <strong>{{ $option['percent'] }}%</strong>
            <span class="post-poll__meter" style="--poll-value: {{ $option['percent'] }}%"></span>
        </button>
    @endforeach
    <p>{{ $poll['votes'] }} votes · {{ $poll['ends'] }}</p>
</div>
