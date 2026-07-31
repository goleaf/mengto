<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

final class ForumMentorshipController extends Controller
{
    public function __invoke(PreviewService $preview): View
    {
        return view('forum.mentorship', [
            'owner' => $preview->ownerData(),
            'page_title' => __('forum_mentorship.page.title'),
            'active_section' => 'forum',
        ]);
    }
}
