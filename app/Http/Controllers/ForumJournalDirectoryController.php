<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumJournal;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class ForumJournalDirectoryController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('viewAny', ForumJournal::class);

        return view('forum.journals.index', [
            'page_title' => __('forum_journals.page.title'),
            'active_section' => 'forum',
        ]);
    }
}
