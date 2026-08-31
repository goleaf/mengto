<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

final class ForumMentorshipController extends Controller
{
    public function __invoke(): View
    {
        return view('forum.mentorship', [
            'page_title' => __('forum_mentorship.page.title'),
            'active_section' => 'forum',
        ]);
    }
}
