@props(['comment', 'post'])

<article
    role="listitem"
    @class([
        'comment-item',
        'comment-item--mine' => $comment['mine'],
        'comment-item--reply' => ($comment['parent'] ?? '') !== '',
    ])
>
    <x-initials-avatar
        :initials="$comment['initials']"
        :tone="$comment['tone']"
        size="regular"
        class="comment-item__avatar"
    />

    <div class="comment-item__content">
        <header class="comment-item__header">
            <div class="min-w-0">
                <h3 class="comment-item__author">{{ $comment['author'] }}</h3>
                <x-icon-text icon="paw-print" class="mt-1">
                    {{ __('presentation.with_pet', ['pet' => $comment['pet']]) }}
                </x-icon-text>
            </div>
            <time datetime="{{ $comment['datetime'] }}" class="comment-item__time">{{ $comment['time'] }}</time>
        </header>

        <p class="comment-item__body">{{ $comment['body'] }}</p>

        <details class="comment-reply">
            <summary>
                <x-ui-icon name="reply" size="sm" />
                {{ __('ui.reply_c253f451bd') }}
            </summary>
            <form method="POST" action="{{ route('actions.perform') }}" class="comment-reply__form">
                @csrf
                <input type="hidden" name="action" value="create-comment">
                <input type="hidden" name="target" value="{{ $post['key'] }}">
                <input type="hidden" name="parent" value="{{ $comment['id'] }}">
                <label for="reply-{{ $comment['id'] }}" class="sr-only">{{ __('presentation.reply_to', ['name' => $comment['author']]) }}</label>
                <textarea
                    id="reply-{{ $comment['id'] }}"
                    name="body"
                    rows="2"
                    maxlength="1200"
                    required
                    placeholder="{{ __('presentation.reply_to_placeholder', ['name' => $comment['author']]) }}"
                    class="field field--textarea"
                ></textarea>
                <x-action-control
                    type="submit"
                    label="{{ __('ui.post_reply_860e367626') }}"
                    icon="send"
                    variant="primary"
                    size="compact"
                />
            </form>
        </details>
    </div>
</article>
