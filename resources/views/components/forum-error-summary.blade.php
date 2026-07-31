@props([
    'messages',
    'heading' => __('forum_accessibility.validation.summary'),
])

<div
    {{ $attributes->class('forum-errors') }}
    role="alert"
    aria-live="assertive"
    tabindex="-1"
    data-forum-error-summary
>
    <strong>{{ $heading }}</strong>
    <ul>
        @forelse ($messages as $field => $fieldMessages)
            @foreach ($fieldMessages as $message)
                <li data-error-field="{{ $field }}">{{ $message }}</li>
            @endforeach
        @empty
            <li>{{ $heading }}</li>
        @endforelse
    </ul>
</div>
