@props([
    'profile',
    'section',
    'badges' => [],
    'summaryLabel' => __('ui.profile_summary_b5913ff585'),
    'summaryIcons' => [],
])

<section
    data-section="{{ $section }}"
    {{ $attributes->class(['panel', 'panel--clip', 'profile-hero']) }}
>
    <x-responsive-image
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
            <x-profile-identity
                :profile="$profile"
                :eyebrow="$profile['role']"
                :avatar-alt="$profile['name']"
            />

            <x-action-list
                :actions="$profile['actions']"
                :label="__('presentation.profile_actions', ['name' => $profile['name']])"
            />
        </div>

        <div class="profile-hero__signals">
            <x-status-badge
                :label="$profile['status']"
                icon="circle-check"
                tone="mint"
                size="regular"
            />
            <x-profile-badge-list :badges="$badges" compact />
        </div>

        <x-stat-grid
            :items="$profile['stats']"
            :label="$summaryLabel"
            :icons="$summaryIcons"
            empty="{{ __('ui.profile_summary_unavailable_4509769cdd') }}"
        />
    </div>
</section>
