<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumConfirmation;
use App\Models\ForumConfirmationEvidence;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumConfirmationEvidence>
 */
final class ForumConfirmationEvidenceFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_confirmation_id' => ForumConfirmation::factory(),
            'submitted_by_user_id' => User::factory(),
            'evidence_type' => 'source-link',
            'summary' => fake()->sentence(),
            'source_url' => 'https://example.test/evidence/'.fake()->uuid(),
            'status' => 'submitted',
            'metadata' => [],
        ];
    }
}
