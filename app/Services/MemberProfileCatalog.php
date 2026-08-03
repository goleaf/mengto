<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContentPublicationType;
use App\Models\ContentPublication;
use App\Models\PetProfile;
use App\Models\SocialActor;
use App\Models\User;
use InvalidArgumentException;

final readonly class MemberProfileCatalog
{
    public function __construct(
        private LocaleFormatter $formatter,
        private SocialAccountActorQuery $accountActors,
        private SocialBlockService $blocks,
        private ContentFeedPresenter $publications,
    ) {}

    /** @return array<string, mixed> */
    public function present(SocialActor $actor, ?User $viewer): array
    {
        $member = $actor->user;

        if (! $member instanceof User) {
            throw new InvalidArgumentException((string) __('member_profiles.errors.invalid_actor'));
        }

        $viewerActorIds = $viewer === null
            ? []
            : $this->accountActors->controlledBy($viewer)->modelKeys();
        $blockedActorIds = $viewer === null
            ? []
            : $this->blocks->blockedActorIdsFor(
                $viewer,
                $this->blocks->blockedUserIdsFor($viewer),
                $viewerActorIds,
            );

        $pets = PetProfile::query()
            ->visibleTo(null)
            ->select([
                'id', 'user_id', 'profile_key', 'slug', 'name', 'species', 'breed',
                'visibility', 'status', 'is_discoverable', 'published_at',
            ])
            ->where('user_id', $member->id)
            ->when($blockedActorIds !== [], fn ($query) => $query->whereDoesntHave(
                'socialActor',
                fn ($actor) => $actor->whereIn('id', $blockedActorIds),
            ))
            ->orderByDesc('published_at')
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->map(static fn (PetProfile $pet): array => [
                'name' => $pet->name,
                'description' => implode(' · ', array_filter([$pet->species, $pet->breed])),
                'url' => route('pets.profile', $pet),
            ])
            ->all();

        $posts = ContentPublication::query()
            ->feedFields()
            ->visibleTo($viewer, $viewerActorIds, $blockedActorIds)
            ->where('publishing_actor_id', $actor->id)
            ->where('content_type', ContentPublicationType::Post->value)
            ->with([
                'publishingActor' => fn ($query) => $query->directoryFields(),
                'publishingActor.user:id,name',
                'audienceRule:id,content_publication_id,audience_type',
                'interactionSettings:id,content_publication_id,allow_comments,allow_reactions,allow_reposts,allow_external_sharing,allow_media_downloads',
                'domainLinks:id,content_publication_id,domain_type,domain_key,relationship,is_primary',
            ])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (ContentPublication $publication): array => $this->publications
                ->present($publication, $viewer))
            ->all();

        return [
            'name' => $member->name,
            'description' => __('member_profiles.page.description'),
            'status' => __('member_profiles.page.public_status'),
            'details' => [
                [
                    'label' => __('member_profiles.details.member_type'),
                    'value' => $actor->actor_type->label(),
                ],
                [
                    'label' => __('member_profiles.details.joined'),
                    'value' => $this->formatter->monthYear($member->created_at),
                ],
            ],
            'pets' => $pets,
            'posts' => $posts,
        ];
    }
}
