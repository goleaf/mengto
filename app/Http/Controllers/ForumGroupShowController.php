<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumGroup;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class ForumGroupShowController extends Controller
{
    public function __invoke(ForumGroup $forumGroup, PreviewService $preview): View
    {
        Gate::authorize('view', $forumGroup);

        return view('forum.groups.show', [
            'owner' => $preview->ownerData(),
            'page_title' => $forumGroup->displayName(),
            'active_section' => 'forum',
            'group_id' => $forumGroup->id,
        ]);
    }
}
