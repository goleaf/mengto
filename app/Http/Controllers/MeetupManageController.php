<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class MeetupManageController extends Controller
{
    public function __invoke(ForumEvent $event): View
    {
        Gate::authorize('manageRegistrations', $event);

        return view('meetups.show', [
            'page_title' => $event->title,
            'active_section' => 'meetups',
            'event_id' => $event->id,
            'workspace_mode' => 'manage',
        ]);
    }
}
