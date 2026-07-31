<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumGroup;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssociateKnowledgeGuideWithGroup
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(
        User $actor,
        ForumGroup $group,
        KnowledgeArticle $article,
    ): KnowledgeArticle {
        $this->gate->forUser($actor)->authorize('createContent', $group);
        $this->gate->forUser($actor)->authorize('update', $article);

        return DB::transaction(function () use ($actor, $group, $article): KnowledgeArticle {
            $lockedArticle = KnowledgeArticle::query()
                ->lockForUpdate()
                ->findOrFail($article->id);
            $this->gate->forUser($actor)->authorize('createContent', $group);
            $this->gate->forUser($actor)->authorize('update', $lockedArticle);

            if ($lockedArticle->forum_group_id !== null
                && $lockedArticle->forum_group_id !== $group->id
            ) {
                throw ValidationException::withMessages([
                    'guide' => __('forum_polls.validation.guide_already_grouped'),
                ]);
            }

            $lockedArticle->forceFill(['forum_group_id' => $group->id])->save();

            return $lockedArticle->refresh();
        }, 3);
    }
}
