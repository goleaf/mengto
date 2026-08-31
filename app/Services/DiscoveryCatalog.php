<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContentPublicationType;
use App\Enums\DiscoveryCategory;
use App\Enums\DiscoveryPreferenceScope;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventVisibility;
use App\Enums\ForumGroupVisibility;
use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Enums\UserStatus;
use App\Models\ContentPublication;
use App\Models\DiscoveryPreference;
use App\Models\ExpertProfile;
use App\Models\ForumEvent;
use App\Models\ForumGroup;
use App\Models\PetProfile;
use App\Models\Place;
use App\Models\SocialActor;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final readonly class DiscoveryCatalog
{
    public function __construct(
        private LocaleFormatter $formatter,
        private SocialBlockService $blocks,
        private SocialAccountActorQuery $accountActors,
        private EventCatalog $eventMedia,
        private GroupCatalog $groupMedia,
        private PlaceCatalog $placeMedia,
    ) {}

    /** @return array<string, mixed> */
    public function browse(User $user, string $query, DiscoveryCategory $activeCategory): array
    {
        $preferences = DiscoveryPreference::query()
            ->forUser($user)
            ->select(['id', 'scope', 'category', 'target_key'])
            ->orderBy('id')
            ->get();
        $blockedUserIds = $this->blocks->blockedUserIdsFor($user);
        $viewerActorIds = $this->accountActors->controlledBy($user)->modelKeys();
        $blockedActorIds = $this->blocks->blockedActorIdsFor(
            $user,
            $blockedUserIds,
            $viewerActorIds,
        );
        $hiddenCategories = $preferences
            ->where('scope', DiscoveryPreferenceScope::Category)
            ->map(static fn (DiscoveryPreference $preference): string => $preference->category->value)
            ->all();
        $categories = $this->categories($query, $activeCategory, $hiddenCategories);
        $selectedCategories = $activeCategory === DiscoveryCategory::All
            ? DiscoveryCategory::recommendationCategories()
            : [$activeCategory];
        $limit = $activeCategory === DiscoveryCategory::All ? 3 : 12;
        $sections = [];

        foreach ($selectedCategories as $category) {
            if (in_array($category->value, $hiddenCategories, true)) {
                continue;
            }

            $hiddenKeys = $preferences
                ->where('scope', DiscoveryPreferenceScope::Item)
                ->where('category', $category)
                ->pluck('target_key')
                ->all();
            $items = $this->items(
                category: $category,
                user: $user,
                query: $query,
                hiddenKeys: $hiddenKeys,
                blockedUserIds: $blockedUserIds,
                blockedActorIds: $blockedActorIds,
                viewerActorIds: $viewerActorIds,
                limit: $limit,
            );

            if ($items !== [] || $activeCategory === $category) {
                $sections[] = [
                    'category' => $category->value,
                    'title' => $category->label(),
                    'description' => $category->description(),
                    'icon' => $category->icon(),
                    'directory_url' => $this->directoryUrl($category, $query),
                    'items' => $items,
                ];
            }
        }

        $resultCount = array_sum(array_map(
            static fn (array $section): int => count($section['items']),
            $sections,
        ));

        return [
            'summary' => [
                'eyebrow' => __('discovery.page.eyebrow'),
                'title' => __('discovery.page.title'),
                'description' => __('discovery.page.description'),
                'count' => trans_choice('discovery.page.result_count', $resultCount, [
                    'count' => $this->formatter->number($resultCount),
                ]),
            ],
            'query' => $query,
            'activeCategory' => $activeCategory->value,
            'categories' => $categories,
            'sections' => $sections,
            'resultCount' => $resultCount,
            'hiddenPreferenceCount' => $preferences->count(),
            'activeCategoryHidden' => in_array($activeCategory->value, $hiddenCategories, true),
        ];
    }

    /**
     * @param  list<string>  $hiddenCategories
     * @return list<array<string, mixed>>
     */
    private function categories(
        string $query,
        DiscoveryCategory $activeCategory,
        array $hiddenCategories,
    ): array {
        return collect(DiscoveryCategory::cases())
            ->map(fn (DiscoveryCategory $category): array => [
                'value' => $category->value,
                'label' => $category->label(),
                'description' => $category->description(),
                'icon' => $category->icon(),
                'active' => $activeCategory === $category,
                'hidden' => in_array($category->value, $hiddenCategories, true),
                'url' => route('discover.index', array_filter([
                    'q' => $query,
                    'category' => $category === DiscoveryCategory::All ? null : $category->value,
                ], static fn (mixed $value): bool => is_string($value) && $value !== '')),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $hiddenKeys
     * @param  list<int>  $blockedUserIds
     * @param  list<int>  $blockedActorIds
     * @param  list<int>  $viewerActorIds
     * @return list<array<string, mixed>>
     */
    private function items(
        DiscoveryCategory $category,
        User $user,
        string $query,
        array $hiddenKeys,
        array $blockedUserIds,
        array $blockedActorIds,
        array $viewerActorIds,
        int $limit,
    ): array {
        return match ($category) {
            DiscoveryCategory::Events => $this->events($query, $hiddenKeys, $blockedUserIds, $limit),
            DiscoveryCategory::Groups => $this->groups(
                $user,
                $query,
                $hiddenKeys,
                $blockedUserIds,
                $blockedActorIds,
                $limit,
            ),
            DiscoveryCategory::Places => $this->places($query, $hiddenKeys, $blockedUserIds, $limit),
            DiscoveryCategory::Experts => $this->experts($query, $hiddenKeys, $blockedUserIds, $blockedActorIds, $limit),
            DiscoveryCategory::Pets => $this->pets($user, $query, $hiddenKeys, $blockedUserIds, $blockedActorIds, $limit),
            DiscoveryCategory::Owners => $this->owners($user, $query, $hiddenKeys, $blockedActorIds, $viewerActorIds, $limit),
            DiscoveryCategory::Posts => $this->posts($user, $query, $hiddenKeys, $blockedActorIds, $viewerActorIds, $limit),
            DiscoveryCategory::All => [],
        };
    }

    /**
     * @param  list<string>  $hiddenKeys
     * @param  list<int>  $blockedUserIds
     * @return list<array<string, mixed>>
     */
    private function events(string $query, array $hiddenKeys, array $blockedUserIds, int $limit): array
    {
        $media = collect($this->eventMedia->all())->keyBy('key');

        return ForumEvent::query()
            ->select([
                'id', 'stable_key', 'title', 'summary', 'type', 'format', 'status',
                'visibility', 'starts_at', 'ends_at', 'timezone', 'location_scope',
                'organizer_user_id', 'organizer_name', 'pet_participation_mode',
                'accessibility_status', 'cost_minor', 'currency', 'archived_at',
            ])
            ->where('visibility', ForumEventVisibility::Public->value)
            ->whereNull('archived_at')
            ->where('ends_at', '>=', now())
            ->whereIn('status', collect(ForumEventStatus::cases())
                ->filter(static fn (ForumEventStatus $status): bool => $status->isDiscoverable())
                ->map(static fn (ForumEventStatus $status): string => $status->value)
                ->all())
            ->when($hiddenKeys !== [], fn (Builder $builder): Builder => $builder->whereNotIn('stable_key', $hiddenKeys))
            ->when($blockedUserIds !== [], fn (Builder $builder): Builder => $builder->where(function (Builder $owner) use ($blockedUserIds): void {
                $owner->whereNull('organizer_user_id')->orWhereNotIn('organizer_user_id', $blockedUserIds);
            }))
            ->when($query !== '', fn (Builder $builder): Builder => $builder->where(function (Builder $search) use ($query): void {
                $like = "%{$query}%";
                $search
                    ->where('title', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('organizer_name', 'like', $like)
                    ->orWhere('location_scope', 'like', $like);
            }))
            ->orderBy('starts_at')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (ForumEvent $event): array => [
                'key' => $event->stable_key,
                'category' => DiscoveryCategory::Events->value,
                'category_label' => DiscoveryCategory::Events->label(),
                'icon' => DiscoveryCategory::Events->icon(),
                'title' => $event->title,
                'description' => Str::of($event->summary)->squish()->limit(180)->toString(),
                'status' => $event->status->label(),
                'status_tone' => $this->eventTone($event->status),
                'meta' => [
                    ['icon' => 'calendar-days', 'label' => (string) $this->formatter->dateTime($event->starts_at, $event->timezone)],
                    ['icon' => $event->format->value === 'online' ? 'video' : 'map-pin', 'label' => $event->location_scope ?: $event->format->label()],
                ],
                'reason' => $event->location_scope
                    ? __('discovery.reasons.upcoming_in_region', ['region' => $event->location_scope])
                    : __('discovery.reasons.upcoming_public_event'),
                'url' => route('meetups.show', $event),
                'image' => data_get($media->get($event->stable_key), 'image'),
                'image_alt' => data_get(
                    $media->get($event->stable_key),
                    'image_alt',
                    __('discovery.media.event', ['title' => $event->title]),
                ),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $hiddenKeys
     * @param  list<int>  $blockedUserIds
     * @param  list<int>  $blockedActorIds
     * @return list<array<string, mixed>>
     */
    private function groups(
        User $user,
        string $query,
        array $hiddenKeys,
        array $blockedUserIds,
        array $blockedActorIds,
        int $limit,
    ): array {
        $media = collect($this->groupMedia->all())->keyBy('key');

        return ForumGroup::query()
            ->discoverableTo($user)
            ->select([
                'id', 'owner_user_id', 'stable_key', 'is_system_managed', 'name',
                'name_translation_key', 'description', 'description_translation_key',
                'visibility', 'status', 'location_scope', 'active_member_count',
                'updated_at',
            ])
            ->whereIn('visibility', [
                ForumGroupVisibility::Public->value,
                ForumGroupVisibility::RequestToJoin->value,
            ])
            ->where(function (Builder $actors) use ($blockedActorIds): void {
                $actors
                    ->whereDoesntHave('socialActor')
                    ->orWhereHas('socialActor', fn (Builder $actor): Builder => $this->recommendableActor($actor, $blockedActorIds));
            })
            ->when($hiddenKeys !== [], fn (Builder $builder): Builder => $builder->whereNotIn('stable_key', $hiddenKeys))
            ->when($blockedUserIds !== [], fn (Builder $builder): Builder => $builder->whereNotIn('owner_user_id', $blockedUserIds))
            ->when($query !== '', fn (Builder $builder): Builder => $builder->where(function (Builder $search) use ($query): void {
                $like = "%{$query}%";
                $search
                    ->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('location_scope', 'like', $like);
            }))
            ->orderByDesc('updated_at')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (ForumGroup $group): array => [
                'key' => $group->stable_key,
                'category' => DiscoveryCategory::Groups->value,
                'category_label' => DiscoveryCategory::Groups->label(),
                'icon' => DiscoveryCategory::Groups->icon(),
                'title' => $group->displayName(),
                'description' => Str::of($group->displayDescription())->squish()->limit(180)->toString(),
                'status' => $group->visibility->label(),
                'status_tone' => $group->visibility === ForumGroupVisibility::Public ? 'positive' : 'community',
                'meta' => [
                    ['icon' => 'users', 'label' => trans_choice('discovery.meta.members', $group->active_member_count, ['count' => $this->formatter->number($group->active_member_count)])],
                    ['icon' => 'map-pin', 'label' => $group->location_scope ?: __('discovery.meta.portal_wide')],
                ],
                'reason' => $group->location_scope
                    ? __('discovery.reasons.community_in_region', ['region' => $group->location_scope])
                    : __('discovery.reasons.active_public_community'),
                'url' => route('groups.show', $group),
                'image' => data_get($media->get($group->stable_key), 'image'),
                'image_alt' => data_get(
                    $media->get($group->stable_key),
                    'image_alt',
                    __('discovery.media.group', ['name' => $group->displayName()]),
                ),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $hiddenKeys
     * @param  list<int>  $blockedUserIds
     * @return list<array<string, mixed>>
     */
    private function places(string $query, array $hiddenKeys, array $blockedUserIds, int $limit): array
    {
        $media = collect($this->placeMedia->demoRecords())->keyBy('key');

        return Place::query()
            ->publiclyDiscoverable()
            ->select([
                'id', 'owner_user_id', 'stable_key', 'slug', 'name', 'summary',
                'type', 'catalog_category', 'public_region', 'verification_status',
                'accessibility_status', 'archived_at', 'updated_at',
            ])
            ->when($hiddenKeys !== [], fn (Builder $builder): Builder => $builder->whereNotIn('stable_key', $hiddenKeys))
            ->when($blockedUserIds !== [], fn (Builder $builder): Builder => $builder->where(function (Builder $owner) use ($blockedUserIds): void {
                $owner->whereNull('owner_user_id')->orWhereNotIn('owner_user_id', $blockedUserIds);
            }))
            ->when($query !== '', fn (Builder $builder): Builder => $builder->where(function (Builder $search) use ($query): void {
                $like = "%{$query}%";
                $search
                    ->where('name', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('public_region', 'like', $like);
            }))
            ->orderByDesc('updated_at')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (Place $place): array => [
                'key' => $place->stable_key,
                'category' => DiscoveryCategory::Places->value,
                'category_label' => DiscoveryCategory::Places->label(),
                'icon' => DiscoveryCategory::Places->icon(),
                'title' => $place->name,
                'description' => Str::of($place->summary ?? $place->type->label())->squish()->limit(180)->toString(),
                'status' => $place->verification_status->label(),
                'status_tone' => $place->verification_status->value === 'verified' ? 'verified' : 'community',
                'meta' => [
                    ['icon' => 'map-pin', 'label' => $place->public_region],
                    ['icon' => 'tag', 'label' => $place->type->label()],
                ],
                'reason' => __('discovery.reasons.public_place_in_region', ['region' => $place->public_region]),
                'url' => route('places.show', $place),
                'image' => data_get($media->get($place->stable_key), 'image'),
                'image_alt' => data_get(
                    $media->get($place->stable_key),
                    'image_alt',
                    __('discovery.media.place', ['name' => $place->name]),
                ),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $hiddenKeys
     * @param  list<int>  $blockedUserIds
     * @param  list<int>  $blockedActorIds
     * @return list<array<string, mixed>>
     */
    private function experts(
        string $query,
        array $hiddenKeys,
        array $blockedUserIds,
        array $blockedActorIds,
        int $limit,
    ): array {
        return ExpertProfile::query()
            ->forDirectory()
            ->published()
            ->where(function (Builder $actors) use ($blockedActorIds): void {
                $actors
                    ->whereDoesntHave('socialActor')
                    ->orWhereHas('socialActor', fn (Builder $actor): Builder => $this->recommendableActor($actor, $blockedActorIds));
            })
            ->when($hiddenKeys !== [], fn (Builder $builder): Builder => $builder->whereNotIn('slug', $hiddenKeys))
            ->when($blockedUserIds !== [], fn (Builder $builder): Builder => $builder->where(function (Builder $owner) use ($blockedUserIds): void {
                $owner->whereNull('owner_id')->orWhereNotIn('owner_id', $blockedUserIds);
            }))
            ->when($query !== '', fn (Builder $builder): Builder => $builder->search($query))
            ->orderByDesc('qualification_verified')
            ->orderByDesc('accepts_new_clients')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (ExpertProfile $expert): array => [
                'key' => $expert->slug,
                'category' => DiscoveryCategory::Experts->value,
                'category_label' => DiscoveryCategory::Experts->label(),
                'icon' => DiscoveryCategory::Experts->icon(),
                'title' => $expert->public_name,
                'description' => Str::of($expert->headline)->squish()->limit(180)->toString(),
                'status' => $expert->qualification_verified
                    ? __('discovery.status.qualification_verified')
                    : $expert->verification_status->label(),
                'status_tone' => $expert->qualification_verified ? 'verified' : 'community',
                'meta' => [
                    ['icon' => 'map-pin', 'label' => implode(', ', array_filter([$expert->city, $expert->country]))],
                    ['icon' => 'calendar-check', 'label' => $expert->accepts_new_clients ? __('discovery.meta.accepting_clients') : __('discovery.meta.profile_available')],
                ],
                'reason' => $expert->qualification_verified
                    ? __('discovery.reasons.verified_specialist')
                    : __('discovery.reasons.published_specialist'),
                'url' => route('experts.show', $expert),
                'image' => $expert->avatar_url,
                'image_alt' => __('discovery.media.expert', ['name' => $expert->public_name]),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $hiddenKeys
     * @param  list<int>  $blockedUserIds
     * @param  list<int>  $blockedActorIds
     * @return list<array<string, mixed>>
     */
    private function pets(
        User $user,
        string $query,
        array $hiddenKeys,
        array $blockedUserIds,
        array $blockedActorIds,
        int $limit,
    ): array {
        return PetProfile::query()
            ->visibleTo($user)
            ->select([
                'id', 'user_id', 'profile_key', 'slug', 'name', 'species', 'breed',
                'visibility', 'status', 'is_discoverable', 'profile_data', 'published_at',
            ])
            ->where('user_id', '!=', $user->id)
            ->where(function (Builder $actors) use ($blockedActorIds): void {
                $actors
                    ->whereDoesntHave('socialActor')
                    ->orWhereHas('socialActor', fn (Builder $actor): Builder => $this->recommendableActor($actor, $blockedActorIds));
            })
            ->when($hiddenKeys !== [], fn (Builder $builder): Builder => $builder->whereNotIn('profile_key', $hiddenKeys))
            ->when($blockedUserIds !== [], fn (Builder $builder): Builder => $builder->whereNotIn('user_id', $blockedUserIds))
            ->when($query !== '', fn (Builder $builder): Builder => $builder->where(function (Builder $search) use ($query): void {
                $like = "%{$query}%";
                $search
                    ->where('name', 'like', $like)
                    ->orWhere('species', 'like', $like)
                    ->orWhere('breed', 'like', $like);
            }))
            ->orderByDesc('published_at')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(function (PetProfile $pet): array {
                $profileData = $pet->profile_data ?? [];

                return [
                    'key' => $pet->profile_key,
                    'category' => DiscoveryCategory::Pets->value,
                    'category_label' => DiscoveryCategory::Pets->label(),
                    'icon' => DiscoveryCategory::Pets->icon(),
                    'title' => $pet->name,
                    'description' => implode(' · ', array_filter([$pet->species, $pet->breed])),
                    'status' => $pet->status->label(),
                    'status_tone' => 'community',
                    'meta' => [
                        ['icon' => 'paw-print', 'label' => $pet->species],
                        ['icon' => 'shield-check', 'label' => __('discovery.meta.public_profile')],
                    ],
                    'reason' => __('discovery.reasons.public_pet_profile'),
                    'url' => route('pets.profile', $pet),
                    'image' => $profileData['card_image'] ?? $profileData['profile_image'] ?? null,
                    'image_alt' => $profileData['card_image_alt'] ?? __('discovery.media.pet', ['name' => $pet->name]),
                ];
            })
            ->all();
    }

    /**
     * @param  list<string>  $hiddenKeys
     * @param  list<int>  $blockedActorIds
     * @param  list<int>  $viewerActorIds
     * @return list<array<string, mixed>>
     */
    private function owners(
        User $user,
        string $query,
        array $hiddenKeys,
        array $blockedActorIds,
        array $viewerActorIds,
        int $limit,
    ): array {
        return SocialActor::query()
            ->directoryFields()
            ->where('is_discoverable', true)
            ->when($blockedActorIds !== [], fn (Builder $actor): Builder => $actor->whereNotIn('id', $blockedActorIds))
            ->where(function (Builder $recommendable): void {
                $recommendable
                    ->whereDoesntHave('settings')
                    ->orWhereHas('settings', fn (Builder $settings): Builder => $settings->where('is_recommendable', true));
            })
            ->with('user:id,name,status,email_verified_at,created_at')
            ->where('actor_type', SocialActorType::User->value)
            ->where('status', SocialActorStatus::Active->value)
            ->whereNot('user_id', $user->id)
            ->when($viewerActorIds !== [], fn (Builder $builder): Builder => $builder->whereNotIn('id', $viewerActorIds))
            ->whereHas('user', fn (Builder $member): Builder => $member
                ->where('status', UserStatus::Active->value)
                ->whereNotNull('email_verified_at'))
            ->when($hiddenKeys !== [], fn (Builder $builder): Builder => $builder->whereNotIn('actor_key', $hiddenKeys))
            ->when($query !== '', fn (Builder $builder): Builder => $builder->whereHas(
                'user',
                fn (Builder $member): Builder => $member->where('name', 'like', "%{$query}%"),
            ))
            ->orderByDesc('updated_at')
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (SocialActor $actor): array => [
                'key' => $actor->actor_key,
                'category' => DiscoveryCategory::Owners->value,
                'category_label' => DiscoveryCategory::Owners->label(),
                'icon' => DiscoveryCategory::Owners->icon(),
                'title' => $actor->user->name,
                'description' => __('discovery.meta.public_member_description'),
                'status' => __('discovery.status.public_member'),
                'status_tone' => 'community',
                'meta' => [
                    ['icon' => 'user-round', 'label' => $actor->actor_type->label()],
                    ['icon' => 'calendar-days', 'label' => __('discovery.meta.member_since', [
                        'date' => $this->formatter->monthYear($actor->user->created_at),
                    ])],
                ],
                'reason' => __('discovery.reasons.discoverable_member'),
                'url' => route('members.show', $actor),
                'image' => null,
                'image_alt' => __('discovery.media.owner', ['name' => $actor->user->name]),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $hiddenKeys
     * @param  list<int>  $blockedActorIds
     * @param  list<int>  $viewerActorIds
     * @return list<array<string, mixed>>
     */
    private function posts(
        User $user,
        string $query,
        array $hiddenKeys,
        array $blockedActorIds,
        array $viewerActorIds,
        int $limit,
    ): array {
        return ContentPublication::query()
            ->feedFields()
            ->visibleTo($user, $viewerActorIds, $blockedActorIds)
            ->where('content_type', ContentPublicationType::Post->value)
            ->whereHas('publishingActor', fn (Builder $actor): Builder => $this->recommendableActor($actor, $blockedActorIds))
            ->when($viewerActorIds !== [], fn (Builder $builder): Builder => $builder->whereNotIn('publishing_actor_id', $viewerActorIds))
            ->when($hiddenKeys !== [], fn (Builder $builder): Builder => $builder->whereNotIn('publication_key', $hiddenKeys))
            ->when($query !== '', fn (Builder $builder): Builder => $builder->where(function (Builder $search) use ($query): void {
                $like = "%{$query}%";
                $search
                    ->where('title', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('body', 'like', $like);
            }))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (ContentPublication $publication): array {
                $description = Str::of($publication->summary ?: $publication->body ?: '')
                    ->squish()
                    ->limit(180)
                    ->toString();
                $title = $publication->title ?: Str::of($description)->limit(72)->toString();

                return [
                    'key' => $publication->publication_key,
                    'category' => DiscoveryCategory::Posts->value,
                    'category_label' => DiscoveryCategory::Posts->label(),
                    'icon' => DiscoveryCategory::Posts->icon(),
                    'title' => $title ?: __('discovery.meta.untitled_post'),
                    'description' => $description ?: __('discovery.meta.post_without_excerpt'),
                    'status' => $publication->status->label(),
                    'status_tone' => 'positive',
                    'meta' => [
                        ['icon' => 'newspaper', 'label' => $publication->content_type->label()],
                        ['icon' => 'calendar-days', 'label' => (string) $this->formatter->dateTime($publication->published_at)],
                    ],
                    'reason' => __('discovery.reasons.visible_post'),
                    'url' => route('content.show', $publication),
                    'image' => null,
                    'image_alt' => __('discovery.media.post', ['title' => $title]),
                ];
            })
            ->all();
    }

    private function directoryUrl(DiscoveryCategory $category, string $query): string
    {
        if ($category === DiscoveryCategory::Owners) {
            return route('discover.index', array_filter([
                'category' => $category->value,
                'q' => $query,
            ], static fn (string $value): bool => $value !== ''));
        }

        return route($category->directoryRoute());
    }

    /** @param Builder<Model> $query */
    private function recommendableActor(Builder $query, array $blockedActorIds): Builder
    {
        return $query
            ->where('is_discoverable', true)
            ->when($blockedActorIds !== [], fn (Builder $actor): Builder => $actor->whereNotIn('id', $blockedActorIds))
            ->where(function (Builder $recommendable): void {
                $recommendable
                    ->whereDoesntHave('settings')
                    ->orWhereHas('settings', fn (Builder $settings): Builder => $settings->where('is_recommendable', true));
            });
    }

    private function eventTone(ForumEventStatus $status): string
    {
        return match ($status) {
            ForumEventStatus::RegistrationOpen,
            ForumEventStatus::Published,
            ForumEventStatus::Scheduled,
            ForumEventStatus::Live => 'positive',
            ForumEventStatus::Postponed,
            ForumEventStatus::Moved,
            ForumEventStatus::FormatChanged,
            ForumEventStatus::RegistrationPaused => 'warning',
            default => 'surface',
        };
    }
}
