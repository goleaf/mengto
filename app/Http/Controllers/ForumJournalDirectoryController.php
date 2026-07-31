<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumJournal;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class ForumJournalDirectoryController extends Controller
{
    public function __invoke(PreviewService $preview): View
    {
        Gate::authorize('viewAny', ForumJournal::class);

        return view('forum.journals.index', [
            'owner' => $preview->ownerData(),
            'page_title' => __('forum_journals.page.title'),
            'active_section' => 'forum',
        ]);
    }
}
