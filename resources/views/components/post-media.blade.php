@props(['post', 'eager' => false])

@if (count($post['media']) > 0)
    @if ($post['sensitive'])
        <details class="sensitive-media">
            <summary>
                <x-ui-icon name="eye-off" />
                <span>
                    <strong>{{ __('ui.sensitive_media') }}</strong>
                    {{ __('ui.open_only_when_you_are_ready') }}
                </span>
            </summary>
            <x-post-media-gallery :media="$post['media']" :eager="$eager" />
        </details>
    @else
        <x-post-media-gallery :media="$post['media']" :eager="$eager" />
    @endif
@endif
