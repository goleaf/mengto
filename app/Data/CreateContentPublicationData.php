<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ContentAudienceType;
use App\Enums\ContentPublicationStatus;
use App\Enums\ContentPublicationType;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final readonly class CreateContentPublicationData
{
    /**
     * @param  list<int>  $includedActorIds
     * @param  list<int>  $excludedActorIds
     */
    public function __construct(
        public ContentPublicationType $type,
        public ContentPublicationStatus $status,
        public ContentAudienceType $audience,
        public string $language,
        public string $idempotencyKey,
        public ?string $title = null,
        public ?string $summary = null,
        public ?string $body = null,
        public ?int $contextActorId = null,
        public ?string $contextType = null,
        public ?string $contextKey = null,
        public array $includedActorIds = [],
        public array $excludedActorIds = [],
        public ?DateTimeInterface $expiresAt = null,
        public bool $allowComments = true,
        public bool $allowReactions = true,
        public bool $allowReposts = false,
        public bool $allowExternalSharing = false,
        public bool $allowMediaDownloads = false,
        public bool $allowMentions = true,
        public bool $isSearchable = false,
        public bool $allowExternalIndexing = false,
        public bool $showReactionCounts = true,
    ) {}

    public function creationFingerprint(int $publishingActorId): string
    {
        $includedActorIds = array_values(array_unique($this->includedActorIds));
        $excludedActorIds = array_values(array_diff(
            array_unique($this->excludedActorIds),
            $includedActorIds,
        ));

        sort($includedActorIds);
        sort($excludedActorIds);

        $payload = json_encode([
            'publishing_actor_id' => $publishingActorId,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'audience' => $this->audience->value,
            'language' => $this->language,
            'title' => $this->title,
            'summary' => $this->summary,
            'body' => $this->body,
            'context_actor_id' => $this->contextActorId,
            'context_type' => $this->contextType,
            'context_key' => $this->contextKey,
            'included_actor_ids' => $includedActorIds,
            'excluded_actor_ids' => $excludedActorIds,
            'expires_at' => $this->expiresAt === null
                ? null
                : DateTimeImmutable::createFromInterface($this->expiresAt)
                    ->setTimezone(new DateTimeZone('UTC'))
                    ->format('Y-m-d\TH:i:s.u\Z'),
            'allow_comments' => $this->allowComments,
            'allow_reactions' => $this->allowReactions,
            'allow_reposts' => $this->allowReposts,
            'allow_external_sharing' => $this->allowExternalSharing,
            'allow_media_downloads' => $this->allowMediaDownloads,
            'allow_mentions' => $this->allowMentions,
            'is_searchable' => $this->isSearchable,
            'allow_external_indexing' => $this->allowExternalIndexing,
            'show_reaction_counts' => $this->showReactionCounts,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash('sha256', $payload);
    }
}
