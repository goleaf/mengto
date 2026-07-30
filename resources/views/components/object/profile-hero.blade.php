@props([
    'profile',
    'section',
    'badges' => [],
    'summaryLabel' => 'Profile summary',
    'summaryIcons' => [],
])

<section
    data-section="{{ $section }}"
    {{ $attributes->class(['panel', 'panel--clip', 'profile-hero']) }}
>
    <x-ui.responsive-image
        data-profile-cover
        :src="$profile['cover_image']"
        :small="$profile['cover_image_small'] ?? null"
        :medium="$profile['cover_image_medium'] ?? null"
        :alt="$profile['cover_image_alt']"
        :width="1600"
        :height="760"
        :small-width="720"
        :medium-width="1200"
        sizes="(min-width: 1280px) 1216px, calc(100vw - 2rem)"
        :eager="true"
        class="profile-hero__cover"
    />

    <div class="profile-hero__body">
        <div class="profile-hero__identity-row">
            <x-object.profile-identity
                :profile="$profile"
                :eyebrow="$profile['role']"
                :avatar-alt="$profile['name']"
            />

            <x-ui.action-list
                :actions="$profile['actions']"
                :label="$profile['name'].' profile actions'"
            />
        </div>

        <div class="profile-hero__signals">
            <x-ui.status-badge
                :label="$profile['status']"
                icon="circle-check"
                tone="mint"
                size="regular"
            />
            <x-object.profile-badge-list :badges="$badges" compact />
        </div>

        <x-ui.stat-grid
            :items="$profile['stats']"
            :label="$summaryLabel"
            :icons="$summaryIcons"
            empty="Profile summary unavailable."
        />
    </div>
</section>
