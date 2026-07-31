<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\VerificationStatus;
use App\Models\ForumGroup;
use App\Models\ForumUserTrustLevel;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

final class KnowledgeArticlePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        if ($knowledgeArticle->forum_group_id !== null) {
            if ($user?->isActive() !== true) {
                return false;
            }

            $group = ForumGroup::query()->find($knowledgeArticle->forum_group_id);

            return $group !== null
                && Gate::forUser($user)->allows('viewMemberContent', $group)
                && ($knowledgeArticle->status->isPublic()
                    || $this->update($user, $knowledgeArticle));
        }

        return $knowledgeArticle->status->isPublic()
            || ($user !== null && $this->update($user, $knowledgeArticle));
    }

    public function proposeCorrection(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user?->isActive() === true
            && $this->view($user, $knowledgeArticle);
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true
            && ($user->isAdministrator() || $this->hasEditorialTrust($user));
    }

    public function update(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user?->isActive() === true
            && ($knowledgeArticle->forum_group_id === null
                || $this->canViewGroup($user, $knowledgeArticle))
            && (
                $user->isAdministrator()
                || $this->hasActiveRole($user, $knowledgeArticle, [
                    KnowledgeCollaboratorRole::Maintainer,
                    KnowledgeCollaboratorRole::Contributor,
                ])
            );
    }

    private function canViewGroup(User $user, KnowledgeArticle $article): bool
    {
        $group = ForumGroup::query()->find($article->forum_group_id);

        return $group !== null
            && Gate::forUser($user)->allows('viewMemberContent', $group);
    }

    public function manageCollaborators(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user?->isActive() === true
            && (
                $user->isAdministrator()
                || $this->hasActiveRole($user, $knowledgeArticle, [
                    KnowledgeCollaboratorRole::Maintainer,
                ])
            );
    }

    public function manageWorkflow(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $this->manageCollaborators($user, $knowledgeArticle);
    }

    public function communityReview(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user?->isActive() === true
            && $this->hasEditorialTrust($user, ['community-reviewer', 'category-steward'])
            && $this->hasActiveRole($user, $knowledgeArticle, [
                KnowledgeCollaboratorRole::CommunityReviewer,
            ]);
    }

    public function expertReview(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $user?->isActive() === true
            && $this->hasActiveRole($user, $knowledgeArticle, [
                KnowledgeCollaboratorRole::ExpertReviewer,
            ])
            && $user->expertProfiles()
                ->where('verification_status', VerificationStatus::Verified->value)
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('verification_expires_at')
                        ->orWhere('verification_expires_at', '>', now());
                })
                ->exists();
    }

    public function reviewCorrection(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $this->manageWorkflow($user, $knowledgeArticle);
    }

    public function rollback(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $this->manageWorkflow($user, $knowledgeArticle);
    }

    public function export(?User $user, KnowledgeArticle $knowledgeArticle): bool
    {
        return $knowledgeArticle->status->isPublic()
            || ($user !== null && $this->update($user, $knowledgeArticle));
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

    /**
     * @param  list<KnowledgeCollaboratorRole>  $roles
     */
    private function hasActiveRole(
        User $user,
        KnowledgeArticle $article,
        array $roles,
    ): bool {
        return $article->collaborators()
            ->where('user_id', $user->id)
            ->whereIn('role', array_map(
                static fn (KnowledgeCollaboratorRole $role): string => $role->value,
                $roles,
            ))
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * @param  list<string>  $keys
     */
    private function hasEditorialTrust(
        User $user,
        array $keys = ['trusted-contributor', 'community-reviewer', 'category-steward'],
    ): bool {
        return ForumUserTrustLevel::query()
            ->where('user_id', $user->id)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas(
                'level',
                fn (Builder $query): Builder => $query
                    ->where('is_active', true)
                    ->whereIn('stable_key', $keys),
            )
            ->exists();
    }
}
