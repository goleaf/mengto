<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class MeetupCreateController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('create', ForumEvent::class);

        return view('meetups.create', [
            'page_title' => __('forum_events.page.create_heading'),
            'active_section' => 'meetups',
        ]);
    }
}
