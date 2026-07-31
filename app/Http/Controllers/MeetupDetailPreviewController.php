<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumEvent;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class MeetupDetailPreviewController extends Controller
{
    public function __invoke(
        ForumEvent $event,
        PreviewService $preview,
    ): View {
        Gate::authorize('view', $event);

        return view('meetups.show', [
            'owner' => $preview->ownerData(),
            'page_title' => $event->title,
            'active_section' => 'meetups',
            'event_id' => $event->id,
        ]);
    }
}
