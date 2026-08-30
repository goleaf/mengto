<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CredentialStatus;
use App\Enums\ExpertProfileStatus;
use App\Enums\ForumExpertAnswerStatus;
use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Enums\ForumExpertSessionStatus;
use App\Enums\VerificationStatus;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

final class ForumExpertSessionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException(
                'Expert session demo data may only be created in an explicitly allowed environment.',
            );
        }

        $host = User::query()->where('actor_key', 'demo-lithuanian')->firstOrFail();
        $member = User::query()->where('actor_key', 'mia-carter')->firstOrFail();
        $profile = ExpertProfile::query()->firstOrCreate(
            ['slug' => 'demo-lithuanian-animal-trainer'],
            ExpertProfile::factory()->raw([
                'owner_id' => $host->id,
                'owner_key' => $host->actor_key,
                'slug' => 'demo-lithuanian-animal-trainer',
                'public_name' => 'Demo Animal Trainer',
                'legal_name' => 'Demo Animal Trainer',
                'primary_type' => 'dog-trainer',
                'specializations' => ['training', 'behavior'],
                'country' => 'LT',
                'status' => ExpertProfileStatus::Published,
                'verification_status' => VerificationStatus::Verified,
                'verification_expires_at' => now()->addYear(),
            ]),
        );

        $credentialIdentifierHash = hash(
            'sha256',
            'demo-lithuanian-animal-trainer-credential',
        );
        Credential::query()->updateOrCreate(
            [
                'expert_profile_id' => $profile->id,
                'credential_identifier_hash' => $credentialIdentifierHash,
            ],
            Credential::factory()->raw([
                'expert_profile_id' => $profile->id,
                'credential_identifier_hash' => $credentialIdentifierHash,
                'title' => 'Demo training qualification',
                'jurisdiction' => 'LT',
                'status' => CredentialStatus::Verified,
                'scope' => ['dog-trainer'],
                'expires_at' => now()->addYear(),
            ]),
        );

        $opensAt = now()->subHour()->startOfMinute();
        $session = ForumExpertSession::query()->updateOrCreate(
            ['stable_key' => 'demo-dog-training-question-session'],
            [
                'expert_profile_id' => $profile->id,
                'created_by_user_id' => $host->id,
                'creation_idempotency_key' => 'demo-expert-session-create',
                'host_name_snapshot' => $profile->public_name,
                'professional_scope' => 'dog-trainer',
                'jurisdiction' => 'LT',
                'title' => 'Calm dog training community questions',
                'summary' => 'A public educational session about humane training routines and referral boundaries.',
                'locale' => 'en',
                'timezone' => 'Europe/Vilnius',
                'status' => ForumExpertSessionStatus::Published,
                'disclaimer_version' => '2026-07',
                'question_opens_at' => $opensAt,
                'question_closes_at' => $opensAt->clone()->addHours(2),
                'starts_at' => $opensAt->clone()->addHours(3),
                'ends_at' => $opensAt->clone()->addHours(5),
                'archived_by_user_id' => null,
                'archived_at' => null,
                'archive_reason_code' => null,
                'lock_version' => 0,
            ],
        );

        $question = ForumExpertSessionQuestion::query()->updateOrCreate(
            ['idempotency_key' => 'demo-expert-session-question'],
            [
                'forum_expert_session_id' => $session->id,
                'author_user_id' => $member->id,
                'stable_key' => 'demo-dog-training-question',
                'body' => 'How can a family introduce a calm reward-based routine without forcing the dog?',
                'status' => ForumExpertQuestionStatus::Answered,
                'moderation_status' => ForumExpertQuestionModerationStatus::Approved,
                'queue_position' => 1,
                'answered_at' => now(),
                'lock_version' => 2,
            ],
        );

        ForumExpertSessionAnswer::query()->updateOrCreate(
            ['forum_expert_session_question_id' => $question->id],
            [
                'forum_expert_session_id' => $session->id,
                'author_user_id' => $host->id,
                'stable_key' => 'demo-dog-training-answer',
                'idempotency_key' => 'demo-expert-session-answer',
                'body' => 'Start with short predictable routines, reward voluntary participation, and stop when stress signals increase.',
                'source_links' => [[
                    'label' => 'Community safety guidance',
                    'url' => 'https://example.test/animal-training-guidance',
                ]],
                'status' => ForumExpertAnswerStatus::Published,
                'current_version' => 1,
                'answered_at' => now(),
            ],
        );
    }
}
