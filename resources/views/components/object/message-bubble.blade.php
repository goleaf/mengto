@props(['message'])

<div role="listitem" data-message-bubble @class(['flex', 'justify-end' => $message['mine'], 'justify-start' => ! $message['mine']])>
    <div class="max-w-[88%] sm:max-w-[75%]">
        <div
            @class([
                'rounded-lg px-4 py-3 text-sm leading-6',
                'bg-paw-ink text-white' => $message['mine'],
                'border border-paw-line bg-white text-paw-ink shadow-sm' => ! $message['mine'],
            ])
        >
            {{ $message['body'] }}
        </div>
        <p @class(['mt-1 text-xs font-semibold text-paw-muted', 'text-right' => $message['mine']])>
            {{ $message['sender'] }} ·
            <time datetime="{{ $message['datetime'] }}">{{ $message['time'] }}</time>
        </p>
    </div>
</div>
