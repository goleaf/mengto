<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\InviteForumGroupMember;
use App\Actions\RequestForumGroupMembership;
use App\Data\ForumGroupInvitationData;
use App\Data\ForumGroupMembershipRequestData;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use LogicException;

final class ForumGroupDemoSeeder extends Seeder
{
    public function run(
        RequestForumGroupMembership $requestMembership,
        InviteForumGroupMember $inviteMember,
    ): void {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Forum group demo data is restricted to explicitly allowed environments.');
        }

        $owner = User::query()->where('actor_key', 'demo-administrator')->firstOrFail();
        $member = User::query()->where('actor_key', 'mia-carter')->firstOrFail();
        $invitee = User::query()->where('actor_key', 'demo-lithuanian')->firstOrFail();
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
                    'user_id' => $owner->id,
                ],
                [
                    'role' => ForumGroupRole::Owner,
                    'state' => ForumGroupMembershipState::Active,
                    'notification_level' => 'all',
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
    }
}
