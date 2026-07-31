<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContentPublication;
use App\Models\PhotoAsset;
use App\Models\PhotoComment;
use App\Models\PhotoReaction;
use App\Models\Publication;
use App\Models\UserDomainState;

final class ContentCompatibilityReport
{
    /**
     * @return array{
     *     canonical_publications: int,
     *     expert_publications: int,
     *     legacy_photo_assets: int,
     *     legacy_photo_comments: int,
     *     legacy_photo_reactions: int,
     *     encrypted_prototype_state_rows: int
     * }
     */
    public function generate(): array
    {
        return [
            'canonical_publications' => ContentPublication::query()->count(),
            'expert_publications' => Publication::query()->count(),
            'legacy_photo_assets' => PhotoAsset::query()->count(),
            'legacy_photo_comments' => PhotoComment::query()->count(),
            'legacy_photo_reactions' => PhotoReaction::query()->count(),
            'encrypted_prototype_state_rows' => UserDomainState::query()
                ->where('namespace', 'prototype.state.v1')
                ->count(),
        ];
    }
}
