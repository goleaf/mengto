<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumExpertSession;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class ForumExpertSessionDirectoryController extends Controller
{
    public function __invoke(PreviewService $preview): View
    {
        Gate::authorize('viewAny', ForumExpertSession::class);

        return view('forum.expert-sessions.index', [
            'owner' => $preview->ownerData(),
            'page_title' => __('forum_expert_sessions.page.title'),
            'active_section' => 'forum',
        ]);
    }
}
