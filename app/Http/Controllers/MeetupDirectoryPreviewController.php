<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumEvent;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class MeetupDirectoryPreviewController extends Controller
{
    public function __invoke(PreviewService $preview): View
    {
        Gate::authorize('viewAny', ForumEvent::class);

        return view('meetups.index', [
            'owner' => $preview->ownerData(),
            'page_title' => __('forum_events.page.title'),
            'active_section' => 'meetups',
        ]);
    }
}
