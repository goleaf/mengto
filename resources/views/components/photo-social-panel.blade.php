@props(['photo'])

<article class="photo-social" data-photo-panel-content>
    <header class="photo-social__header">
        <x-avatar
            :src="$photo['avatar']"
            size="regular"
            decorative
            class="photo-social__avatar"
        />

        <div>
            <strong>{{ $photo['author'] }}</strong>
            <span>{{ $photo['represented'] }}</span>
        </div>

        <a href="{{ $photo['post_url'] }}" class="photo-social__post-link">
            <x-lucide-external-link class="icon icon--sm" aria-hidden="true" />
            <span>{{ __('ui.photo_viewer_open_post') }}</span>
        </a>
    </header>

    <div class="photo-social__caption">
        @if ($photo['caption'] ?? null)
            <p>{{ $photo['caption'] }}</p>
        @endif
        <small>{{ $photo['alt'] }}</small>
    </div>

    <section
        class="photo-social__reactions"
        aria-labelledby="photo-reactions-{{ $photo['photo_key'] }}"
    >
        <div class="photo-social__section-heading">
            <h2 id="photo-reactions-{{ $photo['photo_key'] }}">
                {{ __('ui.photo_viewer_reactions') }}
            </h2>
            <span>{{ $photo['reaction_total'] }}</span>
        </div>

        <div class="photo-social__reaction-list">
            @forelse ($photo['reaction_items'] as $reaction)
                @auth
                    <x-action-form
                        :action="route('photos.interactions.store')"
                        :payload="[
                            'action' => 'set-reaction',
                            'photo' => $photo['photo_key'],
                            'reaction' => $reaction['value'],
                        ]"
                    >
                        <button
                            type="submit"
                            aria-label="{{ $reaction['label'] }}"
                            aria-pressed="{{ $reaction['selected'] ? 'true' : 'false' }}"
                            class="photo-social__reaction"
                        >
                            <x-dynamic-component
                                :component="'lucide-'.$reaction['icon']"
                                class="icon icon--sm"
                                aria-hidden="true"
                            />
                            <span>{{ $reaction['label'] }}</span>
                            <small>{{ $reaction['count'] }}</small>
                        </button>
                    </x-action-form>
                @else
                    <button
                        type="button"
                        aria-label="{{ $reaction['label'] }}"
                        aria-pressed="{{ $reaction['selected'] ? 'true' : 'false' }}"
                        aria-disabled="true"
                        disabled
                        class="photo-social__reaction"
                    >
                        <x-dynamic-component
                            :component="'lucide-'.$reaction['icon']"
                            class="icon icon--sm"
                            aria-hidden="true"
                        />
                        <span>{{ $reaction['label'] }}</span>
                        <small>{{ $reaction['count'] }}</small>
                    </button>
                @endauth
            @empty
                <p class="photo-social__empty">{{ __('ui.photo_viewer_reactions_unavailable') }}</p>
            @endforelse
        </div>
    </section>

    <section
        class="photo-social__comments"
        aria-labelledby="photo-comments-{{ $photo['photo_key'] }}"
    >
        <div class="photo-social__section-heading">
            <h2 id="photo-comments-{{ $photo['photo_key'] }}">
                {{ __('ui.photo_viewer_comments') }}
            </h2>
            <span>{{ $photo['comment_count'] }}</span>
        </div>

        <div class="photo-social__comment-list" role="list">
            @forelse ($photo['comments'] as $comment)
                <article
                    role="listitem"
                    @class([
                        'photo-social__comment',
                        'photo-social__comment--mine' => $comment['mine'],
                    ])
                >
                    <x-initials-avatar
                        :initials="$comment['initials']"
                        :tone="$comment['mine'] ? 'mint' : 'paper'"
                    />
                    <div>
                        <header>
                            <strong>{{ $comment['author'] }}</strong>
                            <time datetime="{{ $comment['datetime'] }}">{{ $comment['time'] }}</time>
                        </header>
                        <p>{{ $comment['body'] }}</p>
                    </div>
                </article>
            @empty
                <div class="photo-social__empty">
                    <x-lucide-message-circle class="icon" aria-hidden="true" />
                    <p>{{ __('ui.photo_viewer_no_comments') }}</p>
                </div>
            @endforelse
        </div>
    </section>

    @auth
        <form
            method="POST"
            action="{{ route('photos.interactions.store') }}"
            class="photo-social__composer"
        >
            @csrf
            <input type="hidden" name="action" value="create-comment">
            <input type="hidden" name="photo" value="{{ $photo['photo_key'] }}">
            <input type="hidden" name="idempotency_key" value="{{ $photo['comment_idempotency_key'] }}">

            <label for="photo-comment-{{ $photo['photo_key'] }}">
                {{ __('ui.photo_viewer_comment_label') }}
            </label>
            <textarea
                id="photo-comment-{{ $photo['photo_key'] }}"
                name="body"
                rows="3"
                maxlength="1200"
                required
                placeholder="{{ __('ui.photo_viewer_comment_placeholder') }}"
                @if ($errors->has('body') && old('photo') === $photo['photo_key'])
                    aria-invalid="true"
                    aria-describedby="photo-comment-error-{{ $photo['photo_key'] }}"
                @endif
            >{{ old('photo') === $photo['photo_key'] ? old('body') : '' }}</textarea>

            @if ($errors->has('body') && old('photo') === $photo['photo_key'])
                <p
                    id="photo-comment-error-{{ $photo['photo_key'] }}"
                    class="photo-social__error"
                >
                    {{ $errors->first('body') }}
                </p>
            @endif

            <button type="submit" class="photo-social__submit">
                <x-lucide-send class="icon icon--sm" aria-hidden="true" />
                <span>{{ __('ui.photo_viewer_submit_comment') }}</span>
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="photo-social__sign-in">
            <x-lucide-log-in class="icon icon--sm" aria-hidden="true" />
            <span>{{ __('ui.photo_viewer_sign_in') }}</span>
        </a>
    @endauth
</article>
