<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ForumGroupShowController extends Controller
{
    public function __invoke(Request $request, ForumGroup $forumGroup): View
    {
        Gate::authorize('view', $forumGroup);

        return view('forum.groups.show', [
            'page_title' => $forumGroup->displayName(),
            'active_section' => $request->routeIs('groups.*') ? 'groups' : 'forum',
            'group_id' => $forumGroup->id,
        ]);
    }
}
