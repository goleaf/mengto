<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\ChangeForumTrustLevel;
use App\Actions\RequestMentorship;
use App\Actions\RespondToMentorship;
use App\Actions\SaveMentorScope;
use App\Actions\SendMentorshipMessage;
use App\Actions\UpdateMentorProfile;
use App\Data\MentorProfileData;
use App\Data\MentorScopeData;
use App\Data\MentorshipRequestData;
use App\Enums\ForumMentorProfileState;
use App\Enums\ForumMentorshipState;
use App\Enums\ForumMentorshipType;
use App\Models\ForumMentorProfile;
use App\Models\ForumTrustLevel;
use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

final class MentorshipDemoSeeder extends Seeder
{
    public function run(
        ChangeForumTrustLevel $changeTrustLevel,
        UpdateMentorProfile $updateProfile,
        SaveMentorScope $saveScope,
        RequestMentorship $requestMentorship,
        RespondToMentorship $respondToMentorship,
        SendMentorshipMessage $sendMessage,
    ): void {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Mentorship demo data is restricted to explicitly allowed environments.');
        }

        $administrator = User::query()
            ->where('actor_key', 'demo-administrator')
            ->firstOrFail();
        $mentor = User::query()
            ->where('actor_key', 'demo-lithuanian')
            ->firstOrFail();
        $mentee = User::query()
            ->where('actor_key', 'mia-carter')
            ->firstOrFail();
        $mentorLevel = ForumTrustLevel::query()
            ->where('stable_key', 'mentor')
            ->firstOrFail();

        $changeTrustLevel->handle(
            actor: $administrator,
            target: $mentor,
            level: $mentorLevel,
            scopeType: 'global',
            scopeKey: 'global',
            reasonCode: 'demo-mentorship-profile',
            evidence: ['demo' => true],
        );

        $existingProfile = ForumMentorProfile::query()
            ->where('user_id', $mentor->id)
            ->first();
        $expectedLockVersion = $existingProfile instanceof ForumMentorProfile
            ? $existingProfile->lock_version
            : 0;
        $profile = $updateProfile->handle($mentor, new MentorProfileData(
            state: ForumMentorProfileState::Active,
            headline: 'Practical peer mentor for careful first steps',
            summary: 'I share bounded community experience, keep private details on the platform, and refer professional questions appropriately.',
            languages: ['lt', 'en'],
            locationScope: 'lt-vilnius',
            timezone: 'Europe/Vilnius',
            communicationPreferences: ['platform'],
            availability: ['note' => 'Weekday evenings'],
            capacity: 3,
            isPublic: true,
            safetyAcknowledged: true,
            expectedLockVersion: $expectedLockVersion,
        ));
        $scope = $saveScope->handle($mentor, $profile, new MentorScopeData(
            type: ForumMentorshipType::FirstTimeOwner,
            experienceSummary: 'Several years of practical community support for preparing safe routines before an animal arrives.',
            forumCategoryId: null,
            taxonId: null,
            requiresVerifiedExpertise: false,
        ));
        $saveScope->handle($mentor, $profile, new MentorScopeData(
            type: ForumMentorshipType::AdoptionAdaptation,
            experienceSummary: 'Peer experience with gradual household introductions and non-medical adoption adjustment routines.',
            forumCategoryId: null,
            taxonId: null,
            requiresVerifiedExpertise: false,
        ));

        $mentorship = $requestMentorship->handle($mentee, $scope, new MentorshipRequestData(
            message: 'I would like a calm peer check-in while preparing the first-week routine for an adopted animal.',
            language: 'en',
            locationScope: 'lt-vilnius',
            communicationPreference: 'platform',
            safetyAcknowledged: true,
            idempotencyKey: 'demo-mentorship-first-owner-request-v1',
        ));

        if ($mentorship->state === ForumMentorshipState::Requested) {
            $mentorship = $respondToMentorship->handle(
                mentor: $mentor,
                mentorship: $mentorship,
                accept: true,
                response: 'I can help with a bounded first-week checklist and will refer medical questions to a veterinarian.',
                safetyAcknowledged: true,
                expectedLockVersion: $mentorship->lock_version,
            );
        }

        if ($mentorship->state === ForumMentorshipState::Active) {
            $sendMessage->handle(
                sender: $mentor,
                mentorship: $mentorship,
                body: 'Let us begin with a quiet arrival area, a predictable routine, and the contact details for your local veterinarian.',
                idempotencyKey: 'demo-mentorship-mentor-message-v1',
            );
            $sendMessage->handle(
                sender: $mentee,
                mentorship: $mentorship,
                body: 'The arrival area is ready, and I understand that medical questions need a qualified professional.',
                idempotencyKey: 'demo-mentorship-mentee-message-v1',
            );
        }
    }
}
