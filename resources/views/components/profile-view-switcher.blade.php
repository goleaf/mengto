@props([
    'options',
    'audience',
])

<section
    aria-labelledby="profile-preview-title"
    {{ $attributes->class(['profile-preview']) }}
>
    <div class="profile-preview__copy">
        <x-lucide-eye class="icon" aria-hidden="true" />
        <div>
            <h2 id="profile-preview-title" class="profile-preview__title">{{ __('ui.preview_visibility_16789b1616') }}</h2>
            <p class="profile-preview__description">
                {{ __('presentation.viewer_scope', ['audience' => $audience === 'owner' ? __('presentation.profile_owner') : $audience]) }}
            </p>
        </div>
    </div>

    <x-tab-list
        :tabs="$options"
        label="{{ __('ui.preview_profile_as_c6a49fb6eb') }}"
        class="tabs--audience"
    />
</section>
