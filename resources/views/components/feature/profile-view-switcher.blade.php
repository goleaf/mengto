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
            <h2 id="profile-preview-title" class="profile-preview__title">Preview visibility</h2>
            <p class="profile-preview__description">
                Showing what a {{ $audience === 'owner' ? 'profile owner' : $audience }} can see.
            </p>
        </div>
    </div>

    <x-ui.tab-list
        :tabs="$options"
        label="Preview profile as"
        class="tabs--audience"
    />
</section>
