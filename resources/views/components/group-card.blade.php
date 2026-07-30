@props(['group', 'eager' => false])

@php
    $groupKey = $group['key'] ?? \Illuminate\Support\Str::slug($group['name']);
    $joined = $group['joined'] ?? false;
    $primary = $group['primary_action'] ?? [
        'label' => $joined ? 'Joined' : 'Join',
        'icon' => $joined ? 'check' : 'user-plus',
        'variant' => 'paper',
        'endpoint' => route('actions.perform'),
        'payload' => ['action' => 'toggle-group', 'target' => $groupKey, 'label' => $group['name']],
        'active' => $joined,
    ];
@endphp

<x-directory-card data-group-card class="group-card" {{ $attributes }}>
    <x-slot:media>
        <x-card-media
            :src="$group['image']"
            :small="$group['image_small'] ?? null"
            :medium="$group['image_medium'] ?? null"
            :alt="$group['image_alt']"
            :width="1200"
            :height="800"
            sizes="(min-width: 1280px) 292px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
            :eager="$eager"
        >
            <div class="group-card__badges">
                <x-status-badge :label="$group['category']" />
                @if ($group['privacy_label'] ?? null)
                    <x-status-badge
                        :label="$group['privacy_label']"
                        :icon="$group['privacy_icon'] ?? null"
                        tone="paper"
                    />
                @endif
                @if ($group['official'] ?? false)
                    <x-status-badge label="Official" icon="badge-check" tone="mint" />
                @endif
            </div>
        </x-card-media>
    </x-slot:media>

    @if ($group['recommendation_reason'] ?? null)
        <x-recommendation-reason
            :reason="$group['recommendation_reason']"
            class="group-card__reason"
        />
    @else
        <p class="group-card__topic">{{ $group['topic'] }}</p>
    @endif

    <h3 class="group-card__title">
        <x-optional-link
            :route-name="$group['detail_route'] ?? null"
            :route-parameters="$group['detail_parameters'] ?? []"
        >
            {{ $group['name'] }}
        </x-optional-link>
    </h3>
    <p class="group-card__description">{{ $group['description'] }}</p>

    <x-tag-list :items="$group['tags']" empty="Open to new neighbors." reserve class="group-card__tags" />

    <dl class="group-card__metrics">
        <div class="min-w-0">
            <dt>
                <x-icon-text icon="users" class="text-xs">
                    Community
                </x-icon-text>
            </dt>
            <dd class="mt-1 text-sm font-semibold text-paw-ink">{{ $group['members'] }}</dd>
        </div>
        <div class="min-w-0">
            <dt>
                <x-icon-text icon="activity" class="text-xs">
                    Activity
                </x-icon-text>
            </dt>
            <dd class="mt-1 text-sm font-semibold text-paw-ink">{{ $group['activity'] }}</dd>
        </div>
    </dl>

    @if ($group['next_event'] ?? null)
        <x-icon-text icon="calendar-clock" class="group-card__event">
            {{ $group['next_event'] }}
        </x-icon-text>
    @endif

    <div class="group-card__footer">
        <div class="group-card__organizer">
            <x-initials-avatar :initials="$group['organizer_initials']" />
            <div>
                <p>Led by {{ $group['organizer'] }}</p>
                <span>{{ $group['location'] ?? 'Community organizer' }}</span>
            </div>
        </div>

        <div class="group-card__actions">
            @if ($group['secondary_action'] ?? null)
                <x-action-control
                    :label="$group['secondary_action']['label']"
                    :icon="$group['secondary_action']['icon']"
                    :endpoint="$group['secondary_action']['endpoint']"
                    :payload="$group['secondary_action']['payload']"
                    :variant="$group['secondary_action']['variant'] ?? 'quiet'"
                    size="icon"
                    :title="$group['secondary_action']['label']"
                    :aria-label="$group['secondary_action']['label']"
                />
            @endif
            <x-action-control
                :label="$primary['label']"
                :icon="$primary['icon'] ?? null"
                :endpoint="$primary['endpoint'] ?? null"
                :payload="$primary['payload'] ?? []"
                :href="$primary['href'] ?? null"
                :variant="$primary['variant'] ?? 'paper'"
                :active="$primary['active'] ?? false"
                :pressed="$primary['pressed'] ?? null"
            />
        </div>
    </div>
</x-directory-card>
