@props(['threadFirst' => false])

<div {{ $attributes->class(['grid items-start gap-4 md:grid-cols-[18rem_minmax(0,1fr)] lg:grid-cols-[22rem_minmax(0,1fr)]']) }}>
    <div @class(['min-w-0', 'order-2 md:order-1' => $threadFirst])>
        {{ $conversations }}
    </div>

    <div @class(['min-w-0', 'order-1 md:order-2' => $threadFirst])>
        @if ($threadFirst)
            <x-ui.text-link
                :href="route('pet-social.messages.index')"
                icon="arrow-left"
                variant="back"
                class="mb-3 md:hidden"
            >
                Back to conversations
            </x-ui.text-link>
        @endif

        {{ $thread }}
    </div>
</div>
