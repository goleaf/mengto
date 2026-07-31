<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumExpertSession;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class ForumExpertSessionShowController extends Controller
{
    public function __invoke(
        ForumExpertSession $forumExpertSession,
        PreviewService $preview,
    ): View {
        Gate::authorize('view', $forumExpertSession);

        return view('forum.expert-sessions.show', [
            'owner' => $preview->ownerData(),
            'page_title' => $forumExpertSession->title,
            'active_section' => 'forum',
            'session_id' => $forumExpertSession->id,
        ]);
    }
}
