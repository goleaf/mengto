<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\AssociateForumTopicWithGroup;
use App\Actions\AssociateKnowledgeGuideWithGroup;
use App\Actions\CreateForumGroupActivity;
use App\Actions\CreateForumPoll;
use App\Actions\InviteForumGroupMember;
use App\Actions\PublishForumGroupAnnouncement;
use App\Actions\RequestForumGroupMembership;
use App\Actions\StoreForumGroupFile;
use App\Data\CreateForumGroupActivityData;
use App\Data\CreateForumGroupAnnouncementData;
use App\Data\CreateForumPollData;
use App\Data\ForumGroupInvitationData;
use App\Data\ForumGroupMembershipRequestData;
use App\Enums\ForumGroupActivityFormat;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumPollEligibility;
use App\Enums\ForumPollResultVisibility;
use App\Enums\ForumPollType;
use App\Enums\ForumPollVoterVisibility;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\SocialActorResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use LogicException;

final class ForumGroupDemoSeeder extends Seeder
{
    public function run(
        RequestForumGroupMembership $requestMembership,
        InviteForumGroupMember $inviteMember,
        AssociateForumTopicWithGroup $associateTopic,
        AssociateKnowledgeGuideWithGroup $associateGuide,
        CreateForumGroupActivity $createActivity,
        PublishForumGroupAnnouncement $publishAnnouncement,
        CreateForumPoll $createPoll,
        StoreForumGroupFile $storeFile,
        SocialActorResolver $actors,
    ): void {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Forum group demo data is restricted to explicitly allowed environments.');
        }

        $owner = User::query()->where('actor_key', 'demo-administrator')->firstOrFail();
        $member = User::query()->where('actor_key', 'mia-carter')->firstOrFail();
        $invitee = User::query()->where('actor_key', 'demo-lithuanian')->firstOrFail();
        $ownerActor = $actors->forUser($owner);
        $groups = ForumGroup::query()
            ->where('is_system_managed', true)
            ->orderBy('id')
            ->get();

        foreach ($groups as $group) {
            if ($group->owner_user_id === null) {
                $group->forceFill(['owner_user_id' => $owner->id])->save();
            }

            ForumGroupMembership::query()->updateOrCreate(
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
                    'ended_at' => null,
                ],
            );
            $group->forceFill([
                'active_member_count' => $group->memberships()
                    ->where('state', ForumGroupMembershipState::Active->value)
                    ->count(),
            ])->save();
        }

        $publicGroup = $groups->firstWhere('stable_key', 'apartment-pets');
        $requestGroup = $groups->firstWhere('stable_key', 'cat-people');
        $privateGroup = $groups->firstWhere('stable_key', 'foster-network');

        if ($publicGroup instanceof ForumGroup) {
            $requestMembership->handle(
                $member,
                $publicGroup,
                new ForumGroupMembershipRequestData(
                    answers: [],
                    idempotencyKey: 'demo-group-apartment-pets-member-v1',
                ),
            );
        }

        if ($requestGroup instanceof ForumGroup) {
            $requestMembership->handle(
                $member,
                $requestGroup,
                new ForumGroupMembershipRequestData(
                    answers: ['0' => 'I share calm indoor enrichment and privacy-safe local resources.'],
                    idempotencyKey: 'demo-group-cat-people-request-v1',
                ),
            );
        }

        if ($privateGroup instanceof ForumGroup) {
            $inviteMember->handle(
                inviter: $owner,
                group: $privateGroup,
                invitee: $invitee,
                data: new ForumGroupInvitationData(
                    role: ForumGroupRole::Member,
                    message: 'Private invitation for a bounded foster-support community.',
                    expiresAt: CarbonImmutable::now()->addDays(14),
                    idempotencyKey: 'demo-group-foster-network-invite-v1',
                ),
            );
        }

        if (! $publicGroup instanceof ForumGroup) {
            return;
        }

        $topic = ForumTopic::query()
            ->where('slug', 'apartment-pets-accessible-enrichment')
            ->first();

        if (! $topic instanceof ForumTopic) {
            $topic = ForumTopic::factory()->create([
                'author_id' => $owner->id,
                'author_key' => $owner->actor_key,
                'author_name' => $owner->name,
                'author_initials' => 'DA',
                'slug' => 'apartment-pets-accessible-enrichment',
                'title' => 'Accessible enrichment ideas for a quiet apartment group',
                'body' => 'This member discussion collects calm, reversible enrichment ideas while keeping addresses, private routines, and animal medical details out of the thread.',
                'category' => 'care',
                'subcategory' => 'enrichment',
                'tags' => ['group', 'enrichment', 'accessibility'],
                'location' => 'Portland',
            ]);
        }

        $guide = KnowledgeArticle::query()
            ->where('slug', 'apartment-group-privacy-checklist')
            ->first();

        if (! $guide instanceof KnowledgeArticle) {
            $guide = KnowledgeArticle::factory()->create([
                'created_by_user_id' => $owner->id,
                'slug' => 'apartment-group-privacy-checklist',
                'translation_group_key' => 'guide-apartment-group-privacy-checklist',
                'title' => 'Privacy checklist for local animal meetups',
                'summary' => 'A member guide for sharing useful meeting details without exposing homes.',
                'body' => 'Use public meeting points, share only the minimum location detail, and move sensitive coordination to an authorized private channel.',
                'category' => 'care',
                'tags' => ['privacy', 'meetups', 'safety'],
                'contributors' => [$owner->name],
            ]);
        }

        $associateTopic->handle($owner, $publicGroup, $topic);
        $associateGuide->handle($owner, $publicGroup, $guide);
        $createActivity->handle(
            $owner,
            $publicGroup,
            new CreateForumGroupActivityData(
                title: 'Accessible neighborhood walk',
                summary: 'A short, low-pressure public walk with a step-free meeting point.',
                format: ForumGroupActivityFormat::Physical,
                startsAt: CarbonImmutable::now()->addDays(14),
                endsAt: CarbonImmutable::now()->addDays(14)->addHours(2),
                timezone: 'America/Los_Angeles',
                locationScope: $publicGroup->location_scope,
                onlineUrl: null,
                capacity: 18,
                participationNotes: 'Bring individual water and allow animals space.',
                idempotencyKey: 'demo-group-content:activity:v1',
            ),
        );
        $publishAnnouncement->handle(
            $owner,
            $publicGroup,
            new CreateForumGroupAnnouncementData(
                title: 'Privacy-safe meetup guidance',
                body: 'Use the public meeting point and do not post home addresses or private contact details.',
                publishedAt: CarbonImmutable::now(),
                expiresAt: CarbonImmutable::now()->addMonths(6),
                idempotencyKey: 'demo-group-content:announcement:v1',
            ),
        );

        foreach ($this->pollDefinitions() as $definition) {
            $createPoll->handle(
                $owner,
                $publicGroup,
                new CreateForumPollData(...$definition),
            );
        }

        $storeFile->handle(
            $owner,
            $publicGroup,
            UploadedFile::fake()->createWithContent(
                'privacy-checklist.txt',
                "Share public meeting points only.\nKeep private contact details private.\n",
            ),
            'A private checklist available only to current group members.',
            'demo-group-content:file:v1',
        );
    }

    /**
     * @return list<array{
     *     question: string,
     *     description: string,
     *     options: list<string>,
     *     type: ForumPollType,
     *     voterVisibility: ForumPollVoterVisibility,
     *     resultVisibility: ForumPollResultVisibility,
     *     isVoteEditable: bool,
     *     eligibility: ForumPollEligibility,
     *     closesAt: CarbonImmutable,
     *     idempotencyKey: string
     * }>
     */
    private function pollDefinitions(): array
    {
        $closesAt = CarbonImmutable::now()->addDays(21);

        return [
            [
                'question' => 'Which public park entrance is easiest to access?',
                'description' => 'Choose one public meeting point for the next group walk.',
                'options' => ['North gate', 'Visitor center'],
                'type' => ForumPollType::SingleChoice,
                'voterVisibility' => ForumPollVoterVisibility::Anonymous,
                'resultVisibility' => ForumPollResultVisibility::AfterVote,
                'isVoteEditable' => true,
                'eligibility' => ForumPollEligibility::GroupMembers,
                'closesAt' => $closesAt,
                'idempotencyKey' => 'demo-group-content:poll:single:v1',
            ],
            [
                'question' => 'Which accessibility topics should the meetup cover?',
                'description' => 'Members may select more than one practical topic.',
                'options' => ['Step-free routes', 'Quiet spaces', 'Accessible transport'],
                'type' => ForumPollType::MultipleChoice,
                'voterVisibility' => ForumPollVoterVisibility::Visible,
                'resultVisibility' => ForumPollResultVisibility::Public,
                'isVoteEditable' => true,
                'eligibility' => ForumPollEligibility::LocationMembers,
                'closesAt' => $closesAt,
                'idempotencyKey' => 'demo-group-content:poll:multiple:v1',
            ],
            [
                'question' => 'Rank the proposed themes for the next member guide',
                'description' => 'Results become visible when the poll closes.',
                'options' => ['Apartment safety', 'Calm enrichment', 'Travel preparation'],
                'type' => ForumPollType::RankedChoice,
                'voterVisibility' => ForumPollVoterVisibility::Visible,
                'resultVisibility' => ForumPollResultVisibility::AfterClose,
                'isVoteEditable' => false,
                'eligibility' => ForumPollEligibility::GroupMembers,
                'closesAt' => $closesAt,
                'idempotencyKey' => 'demo-group-content:poll:ranked:v1',
            ],
        ];
    }
}
