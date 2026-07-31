<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\KnowledgeCorrectionStatus;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCorrection;
use App\Services\ForumActor;

final class ProposeCorrection
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(KnowledgeArticle $article, array $data): KnowledgeCorrection
    {
        $user = $this->actor->requireUser();

        return $article->corrections()->create([
            'reporter_key' => $user->actor_key,
            'reporter_user_id' => $user->id,
            'field' => $data['field'],
            'suggestion' => trim((string) $data['suggestion']),
            'source_url' => $data['source_url'] ?? null,
            'status' => KnowledgeCorrectionStatus::Submitted,
            'base_version_number' => $article->current_version,
        ]);
    }
}
