<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseEventsRequest;
use App\Services\EventPresenter;
use Illuminate\Contracts\View\View;

class MeetupDetailPreviewController extends Controller
{
    public function __invoke(
        BrowseEventsRequest $request,
        string $event,
        EventPresenter $events,
    ): View {
        $data = $events->detail($event, $request->validated('tab', 'overview'));

        abort_if($data === null, 404);

        return view('pet-social.meetups.show', $data);
    }
}
