@props(['post', 'eager' => false])

@if (count($post['media']) > 0)
    @if ($post['sensitive'])
        <details class="sensitive-media">
            <summary>
                <x-lucide-eye-off class="icon" aria-hidden="true" />
                <span>
                    <strong>Sensitive media</strong>
                    Open only when you are ready.
                </span>
            </summary>
            <x-object.post-media-gallery :media="$post['media']" :eager="$eager" />
        </details>
    @else
        <x-object.post-media-gallery :media="$post['media']" :eager="$eager" />
    @endif
@endif
