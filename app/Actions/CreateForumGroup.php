<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateForumGroupData;
use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumGroupStatus;
use App\Enums\ForumGroupVisibility;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\User;
use App\Services\ForumGroupAudit;
use App\Services\SocialActorResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final readonly class CreateForumGroup
{
    public function __construct(
        private ForumGroupAudit $audit,
        private SocialActorResolver $actors,
    ) {}

    public function handle(User $owner, CreateForumGroupData $data): ForumGroup
    {
        if (! $owner->isActive() || ! $owner->hasVerifiedEmail()) {
            throw new AuthorizationException;
        }

        Validator::make([
            'name' => $data->name,
            'description' => $data->description,
            'rules' => $data->rules,
            'visibility' => $data->visibility->value,
            'default_locale' => $data->defaultLocale,
            'location_scope' => $data->locationScope,
            'membership_questions' => $data->membershipQuestions,
            'taxon_ids' => $data->taxonIds,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'name' => ['required', 'string', 'min:3', 'max:160'],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'rules' => ['required', 'array', 'min:1', 'max:20'],
            'rules.*' => ['required', 'string', 'min:3', 'max:500'],
            'visibility' => ['required', Rule::enum(ForumGroupVisibility::class)],
            'default_locale' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'location_scope' => [
                'nullable',
                'string',
                'max:160',
                'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
            ],
            'membership_questions' => ['array', 'max:10'],
            'membership_questions.*' => ['required', 'string', 'min:3', 'max:300'],
            'taxon_ids' => ['array', 'max:10'],
            'taxon_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('taxa', 'id')->where('is_active', true),
            ],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        return DB::transaction(function () use ($data, $owner): ForumGroup {
            $group = ForumGroup::query()->firstOrCreate(
                ['creation_idempotency_key' => $data->idempotencyKey],
                [
                    'owner_user_id' => $owner->id,
                    'stable_key' => 'group-'.Str::lower((string) Str::ulid()),
                    'is_system_managed' => false,
                    'name' => trim($data->name),
                    'description' => trim($data->description),
                    'rules' => array_values(array_map('trim', $data->rules)),
                    'rules_version' => 1,
                    'visibility' => $data->visibility,
                    'status' => ForumGroupStatus::Active,
                    'default_locale' => $data->defaultLocale,
                    'location_scope' => $data->locationScope,
                    'membership_questions' => array_values(array_map(
                        'trim',
                        $data->membershipQuestions,
                    )),
                    'allowed_actor_types' => ['user', 'pet', 'expert'],
                    'active_member_count' => 1,
                    'lock_version' => 0,
                ],
            );

            if ($group->owner_user_id !== $owner->id) {
                throw new AuthorizationException;
            }

            if (! $group->wasRecentlyCreated) {
                return $this->loadPresentationRelations($group);
            }

            $ownerActor = $this->actors->forUser($owner);
            ForumGroupMembership::query()->firstOrCreate(
                [
                    'forum_group_id' => $group->id,
                    'social_actor_id' => $ownerActor->id,
                ],
                [
                    'user_id' => $owner->id,
                    'role' => ForumGroupRole::Owner,
                    'state' => ForumGroupMembershipState::Active,
                    'notification_level' => 'all',
                    'accepted_rules_version' => $group->rules_version,
                    'accepted_rules_at' => now(),
                    'joined_at' => now(),
                    'last_idempotency_key' => "group:{$group->id}:owner",
                ],
            );
            $group->taxa()->sync($data->taxonIds);

            $this->audit->record(
                group: $group,
                actor: $owner,
                eventType: ForumGroupEventType::Created,
                reasonCode: 'group-created',
                summaryTranslationKey: 'forum_groups.events.created',
                subject: $owner,
                idempotencyKey: "group:{$group->id}:created",
            );

            return $this->loadPresentationRelations($group);
        }, 3);
    }

    private function loadPresentationRelations(ForumGroup $group): ForumGroup
    {
        return $group->load([
            'owner:id,name',
            'taxa:id,stable_key',
            'taxa.activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
        ]);
    }
}
