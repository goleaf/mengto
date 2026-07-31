<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateContentPublicationData;
use App\Enums\ContentAudienceActorEffect;
use App\Enums\ContentAudienceType;
use App\Enums\ContentPublicationEventType;
use App\Enums\ContentPublicationStatus;
use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Models\ContentPublication;
use App\Models\SocialActor;
use App\Models\User;
use App\Services\ContentAudienceCompatibility;
use App\Services\SocialActorAccess;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class CreateContentPublication
{
    public function __construct(
        private readonly SocialActorAccess $actorAccess,
        private readonly ContentAudienceCompatibility $audienceCompatibility,
    ) {}

    public function handle(
        User $user,
        SocialActor $publishingActor,
        CreateContentPublicationData $data,
    ): ContentPublication {
        $publishingRole = $this->authorizeAndValidate($user, $publishingActor, $data);
        $creationFingerprint = $data->creationFingerprint($publishingActor->id);

        return DB::transaction(function () use (
            $user,
            $publishingActor,
            $publishingRole,
            $data,
            $creationFingerprint,
        ): ContentPublication {
            $publication = ContentPublication::query()->firstOrCreate(
                [
                    'real_author_user_id' => $user->id,
                    'idempotency_key' => $data->idempotencyKey,
                ],
                [
                    'publication_key' => (string) Str::ulid(),
                    'publishing_actor_id' => $publishingActor->id,
                    'representation_role' => $publishingRole,
                    'content_type' => $data->type,
                    'status' => $data->status,
                    'language' => $data->language,
                    'title' => $data->title,
                    'summary' => $data->summary,
                    'body' => $data->body,
                    'lock_version' => 1,
                    'creation_fingerprint' => $creationFingerprint,
                    'published_at' => $data->status === ContentPublicationStatus::Published
                        ? now()
                        : null,
                    'expires_at' => $data->expiresAt,
                ],
            );

            if (! $publication->wasRecentlyCreated) {
                if (! hash_equals($publication->creation_fingerprint, $creationFingerprint)) {
                    throw new InvalidArgumentException(
                        __('content.errors.idempotency_conflict'),
                    );
                }

                return $publication->load([
                    'audienceRule.actors',
                    'interactionSettings',
                    'publishingActor',
                    'events',
                ]);
            }

            $rule = $publication->audienceRule()->create([
                'audience_type' => $data->audience,
                'context_actor_id' => $data->contextActorId,
                'context_type' => $data->contextType,
                'context_key' => $data->contextKey,
                'expires_at' => $data->expiresAt,
                'lock_version' => 1,
            ]);

            foreach ($this->audienceActors($data) as $actor) {
                $rule->actors()->create($actor);
            }

            $publication->interactionSettings()->create([
                'allow_comments' => $data->allowComments,
                'allow_reactions' => $data->allowReactions,
                'allow_reposts' => $data->allowReposts,
                'allow_external_sharing' => $data->allowExternalSharing,
                'allow_media_downloads' => $data->allowMediaDownloads,
                'allow_mentions' => $data->allowMentions,
                'is_searchable' => $data->isSearchable,
                'allow_external_indexing' => $data->allowExternalIndexing,
                'show_reaction_counts' => $data->showReactionCounts,
            ]);

            $publication->events()->create([
                'actor_user_id' => $user->id,
                'represented_actor_id' => $publishingActor->id,
                'actor_key_snapshot' => $publishingActor->actor_key,
                'representation_role' => $publishingRole,
                'event_type' => $data->status === ContentPublicationStatus::Published
                    ? ContentPublicationEventType::Published
                    : ContentPublicationEventType::Created,
                'from_status' => null,
                'to_status' => $data->status,
                'idempotency_key' => 'create:'.$data->idempotencyKey,
                'metadata' => [
                    'audience' => $data->audience->value,
                    'content_type' => $data->type->value,
                ],
                'occurred_at' => now(),
            ]);

            return $publication->load([
                'audienceRule.actors',
                'interactionSettings',
                'publishingActor',
                'events',
            ]);
        });
    }

    private function authorizeAndValidate(
        User $user,
        SocialActor $publishingActor,
        CreateContentPublicationData $data,
    ): string {
        $publishingRole = $user->isActive()
            ? $this->actorAccess->publishingRole($publishingActor, $user)
            : null;

        if ($publishingRole === null) {
            throw new AuthorizationException;
        }

        if ($publishingActor->status !== SocialActorStatus::Active) {
            throw new AuthorizationException;
        }

        if (($data->title === null || trim($data->title) === '')
            && ($data->body === null || trim($data->body) === '')
        ) {
            throw new InvalidArgumentException(__('content.errors.missing_content'));
        }

        if (! in_array($data->status, [
            ContentPublicationStatus::Draft,
            ContentPublicationStatus::Published,
        ], true)) {
            throw new InvalidArgumentException(__('content.errors.unsupported_initial_status'));
        }

        if (! $this->audienceCompatibility->allows($publishingActor, $data->audience)) {
            throw new InvalidArgumentException(__('content.errors.broad_audience_not_allowed'));
        }

        if ($data->audience === ContentAudienceType::Selected
            && $data->includedActorIds === []
        ) {
            throw new InvalidArgumentException(__('content.errors.selected_audience_empty'));
        }

        if ($data->audience === ContentAudienceType::Group) {
            $context = $data->contextActorId === null
                ? null
                : SocialActor::query()->directoryFields()->find($data->contextActorId);

            if ($context === null || $context->actor_type !== SocialActorType::Group) {
                throw new InvalidArgumentException(__('content.errors.group_context_required'));
            }
        }

        return $publishingRole;
    }

    /** @return list<array{social_actor_id: int, effect: ContentAudienceActorEffect}> */
    private function audienceActors(CreateContentPublicationData $data): array
    {
        $included = array_map(
            static fn (int $id): array => [
                'social_actor_id' => $id,
                'effect' => ContentAudienceActorEffect::Include,
            ],
            array_values(array_unique($data->includedActorIds)),
        );
        $excludedIds = array_values(array_diff(
            array_unique($data->excludedActorIds),
            $data->includedActorIds,
        ));
        $excluded = array_map(
            static fn (int $id): array => [
                'social_actor_id' => $id,
                'effect' => ContentAudienceActorEffect::Exclude,
            ],
            $excludedIds,
        );

        return [...$included, ...$excluded];
    }
}
