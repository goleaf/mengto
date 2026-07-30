@props(['post', 'eager' => false])

@if (count($post['media']) > 0)
    @if ($post['sensitive'])
        <details class="sensitive-media">
            <summary>
                <x-lucide-eye-off class="icon" aria-hidden="true" />
                <span>
                    <strong>{{ __('ui.sensitive_media_e6a7a0b7b8') }}</strong>
                    {{ __('ui.open_only_when_you_are_ready_d27631ff6e') }}
                </span>
            </summary>
            <x-post-media-gallery :media="$post['media']" :eager="$eager" />
        </details>
    @else
        <x-post-media-gallery :media="$post['media']" :eager="$eager" />
    @endif
@endif
