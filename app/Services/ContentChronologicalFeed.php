<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentPublication;
use App\Models\User;
use Illuminate\Pagination\CursorPaginator;

final class ContentChronologicalFeed
{
    public function __construct(
        private readonly SocialAccountActorQuery $accountActors,
        private readonly SocialBlockService $blocks,
        private readonly ContentFeedPresenter $presenter,
    ) {}

    /** @return array{items: list<array<string, mixed>>, next_url: string|null, previous_url: string|null} */
    public function page(?User $viewer, int $perPage = 15): array
    {
        $viewerActorIds = $viewer === null
            ? []
            : $this->accountActors->controlledBy($viewer)->modelKeys();
        $blockedActorIds = $viewer === null
            ? []
            : $this->blocks->blockedActorIdsFor($viewer);

        /** @var CursorPaginator<int, ContentPublication> $publications */
        $publications = ContentPublication::query()
            ->feedFields()
            ->visibleTo($viewer, $viewerActorIds, $blockedActorIds)
            ->with([
                'publishingActor' => fn ($query) => $query->directoryFields(),
                'publishingActor.user:id,name',
                'publishingActor.petProfile:id,name',
                'publishingActor.expertProfile:id,public_name',
                'publishingActor.forumGroup:id,name,name_translation_key,is_system_managed',
                'audienceRule:id,content_publication_id,audience_type',
                'interactionSettings:id,content_publication_id,allow_comments,allow_reactions,allow_reposts,allow_external_sharing,allow_media_downloads',
                'domainLinks:id,content_publication_id,domain_type,domain_key,relationship,is_primary',
            ])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->cursorPaginate($perPage)
            ->withQueryString();

        return [
            'items' => $publications->getCollection()
                ->map(fn (ContentPublication $publication): array => $this->presenter
                    ->present($publication, $viewer))
                ->values()
                ->all(),
            'next_url' => $publications->nextPageUrl(),
            'previous_url' => $publications->previousPageUrl(),
        ];
    }
}
