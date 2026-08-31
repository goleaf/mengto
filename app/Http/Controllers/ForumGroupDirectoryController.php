<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class ForumGroupDirectoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('viewAny', ForumGroup::class);

        return view('forum.groups.index', [
            'page_title' => __('forum_groups.page.title'),
            'active_section' => $request->routeIs('groups.*') ? 'groups' : 'forum',
        ]);
    }
}
