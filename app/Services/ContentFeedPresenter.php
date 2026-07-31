<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentDomainLink;
use App\Models\ContentPublication;
use App\Models\User;
use Illuminate\Support\Str;

final class ContentFeedPresenter
{
    public function __construct(private readonly SocialActorPresenter $actors) {}

    /** @return array<string, mixed> */
    public function present(ContentPublication $publication, ?User $viewer): array
    {
        $actor = $this->actors->present($publication->publishingActor);
        $timezone = $viewer?->timezone ?: (string) config('app.timezone');

        return [
            'key' => $publication->publication_key,
            'url' => route('content.show', $publication),
            'actor' => $actor,
            'type' => $publication->content_type->value,
            'type_label' => $publication->content_type->label(),
            'status' => $publication->status->value,
            'status_label' => $publication->status->label(),
            'audience' => $publication->audienceRule->audience_type->value,
            'audience_label' => $publication->audienceRule->audience_type->label(),
            'language' => $publication->language,
            'title' => $publication->title,
            'summary' => $publication->summary,
            'body' => $publication->body,
            'excerpt' => $publication->body === null
                ? null
                : Str::limit($publication->body, 420),
            'published_at' => $publication->published_at?->timezone($timezone)
                ->translatedFormat((string) __('content.publication.date_format')),
            'capabilities' => [
                'comments' => $publication->interactionSettings->allow_comments,
                'reactions' => $publication->interactionSettings->allow_reactions,
                'reposts' => $publication->interactionSettings->allow_reposts,
                'external_sharing' => $publication->interactionSettings->allow_external_sharing,
                'media_downloads' => $publication->interactionSettings->allow_media_downloads,
            ],
            'links' => $publication->domainLinks
                ->map(static fn (ContentDomainLink $link): array => [
                    'type' => $link->domain_type->value,
                    'key' => $link->domain_key,
                    'relationship' => $link->relationship,
                ])
                ->all(),
        ];
    }
}
