<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KnowledgeCollaboratorRole;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\User;

/**
 * @extends ApplicationFactory<KnowledgeArticleCollaborator>
 */
final class KnowledgeArticleCollaboratorFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'article_id' => KnowledgeArticle::factory(),
            'user_id' => User::factory(),
            'role' => KnowledgeCollaboratorRole::Contributor,
            'added_by_user_id' => null,
            'attribution_name' => null,
            'revoked_at' => null,
            'revoked_by_user_id' => null,
        ];
    }

    public function maintainer(): static
    {
        return $this->state(fn (): array => [
            'role' => KnowledgeCollaboratorRole::Maintainer,
        ]);
    }

    public function communityReviewer(): static
    {
        return $this->state(fn (): array => [
            'role' => KnowledgeCollaboratorRole::CommunityReviewer,
        ]);
    }

    public function expertReviewer(): static
    {
        return $this->state(fn (): array => [
            'role' => KnowledgeCollaboratorRole::ExpertReviewer,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (): array => [
            'revoked_at' => now()->subDay(),
            'revoked_by_user_id' => User::factory(),
        ]);
    }
}
