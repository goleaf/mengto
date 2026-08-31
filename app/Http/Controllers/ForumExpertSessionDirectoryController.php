<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumExpertSession;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class ForumExpertSessionDirectoryController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('viewAny', ForumExpertSession::class);

        return view('forum.expert-sessions.index', [
            'page_title' => __('forum_expert_sessions.page.title'),
            'active_section' => 'forum',
        ]);
    }
}
