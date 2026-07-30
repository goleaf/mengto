<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseEventsRequest;
use App\Services\PawCircleEventPresenter;
use Illuminate\Contracts\View\View;

class MeetupDirectoryPreviewController extends Controller
{
    public function __invoke(
        BrowseEventsRequest $request,
        PawCircleEventPresenter $events,
    ): View {
        $parameters = $request->validated();

        return view('pet-social.meetups.index', $events->directory(
            $parameters['q'] ?? '',
            $parameters['filter'] ?? 'recommended',
            $parameters['sort'] ?? 'soonest',
            $parameters['view'] ?? 'list',
        ));
    }
}
