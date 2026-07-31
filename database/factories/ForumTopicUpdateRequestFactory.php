<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumTopicUpdateRequestKind;
use App\Enums\ForumTopicUpdateRequestStatus;
use App\Models\ForumTopic;
use App\Models\ForumTopicUpdateRequest;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumTopicUpdateRequest>
 */
final class ForumTopicUpdateRequestFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_topic_id' => ForumTopic::factory(),
            'requester_user_id' => User::factory(),
            'kind' => ForumTopicUpdateRequestKind::UpdateRequest,
            'status' => ForumTopicUpdateRequestStatus::Pending,
            'reason' => fake()->paragraph(),
            'proposed_body' => null,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'resolution_reason' => null,
            'lock_version' => 1,
            'idempotency_key' => 'factory-topic-update-'.Str::uuid(),
            'metadata' => [],
        ];
    }

    public function communityProposal(): static
    {
        return $this->state(fn (): array => [
            'kind' => ForumTopicUpdateRequestKind::CommunityProposal,
            'proposed_body' => fake()->paragraphs(3, true),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumTopicUpdateRequestStatus::Accepted,
            'reviewed_by_user_id' => User::factory(),
            'reviewed_at' => now(),
            'resolution_reason' => fake()->sentence(),
            'lock_version' => 2,
        ]);
    }
}
