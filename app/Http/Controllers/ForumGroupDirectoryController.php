<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumGroup;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class ForumGroupDirectoryController extends Controller
{
    public function __invoke(PreviewService $preview): View
    {
        Gate::authorize('viewAny', ForumGroup::class);

        return view('forum.groups.index', [
            'owner' => $preview->ownerData(),
            'page_title' => __('forum_groups.page.title'),
            'active_section' => 'forum',
        ]);
    }
}
