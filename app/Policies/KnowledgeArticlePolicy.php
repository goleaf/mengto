<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\KnowledgeStatus;
use App\Models\KnowledgeArticle;
use App\Models\User;

final class KnowledgeArticlePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return in_array($knowledgeArticle->status, [
            KnowledgeStatus::Published,
            KnowledgeStatus::Outdated,
        ], true);
    }

    public function proposeCorrection(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user?->isActive() === true
            && $this->view($user, $knowledgeArticle);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true && $user->isAdministrator();
    }

    public function update(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user?->isActive() === true && $user->isAdministrator();
    }

    public function delete(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return false;
    }

    public function restore(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return false;
    }

    public function forceDelete(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return false;
    }
}
