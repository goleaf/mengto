@props([
    'options',
    'audience',
    'copy' => [],
])

<section
    aria-labelledby="profile-preview-title"
    {{ $attributes->class(['profile-preview']) }}
>
    <div class="profile-preview__copy">
        <x-ui-icon name="eye" />
        <div>
            <h2 id="profile-preview-title" class="profile-preview__title">
                {{ $copy['title'] ?? __('ui.preview_visibility') }}
            </h2>
            <p class="profile-preview__description">
                {{ $copy['description'] ?? __('presentation.viewer_scope', ['audience' => $audience === 'owner' ? __('presentation.profile_owner') : $audience]) }}
            </p>
        </div>
    </div>

    <x-tab-list
        :tabs="$options"
        :label="$copy['label'] ?? __('ui.preview_profile_as')"
        code-name="audience"
        class="tabs--audience"
    />
</section>
