<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseEventsRequest;
use App\Services\EventPresenter;
use Illuminate\Contracts\View\View;

class MeetupDirectoryPreviewController extends Controller
{
    public function __invoke(
        BrowseEventsRequest $request,
        EventPresenter $events,
    ): View {
        $parameters = $request->validated();

        return view('meetups.index', $events->directory(
            $parameters['q'] ?? '',
            $parameters['filter'] ?? 'recommended',
            $parameters['sort'] ?? 'soonest',
            $parameters['view'] ?? 'list',
        ));
    }
}
