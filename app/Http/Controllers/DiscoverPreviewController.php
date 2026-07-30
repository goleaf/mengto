<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePawCircleRequest;
use App\Services\PawCircleDirectoryFilter;
use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class DiscoverPreviewController extends Controller
{
    public function __invoke(
        BrowsePawCircleRequest $request,
        PawCirclePreviewService $preview,
        PawCircleDirectoryFilter $filter,
    ): View {
        $data = $preview->discoverData();
        $parameters = $request->validated();
        $data['results'] = $filter->apply(
            $data['results'],
            $parameters['q'] ?? null,
            $parameters['filter'] ?? null,
            null,
            [
                'pets' => ['pet'],
                'people' => ['neighbor'],
                'meetups' => ['meetup'],
                'groups' => ['group'],
            ],
            ['kind', 'title', 'meta', 'description', 'detail', 'tags'],
        );

        return view('pet-social.discover.index', [
            ...$data,
            'directoryQuery' => $parameters['q'] ?? '',
            'activeFilter' => $parameters['filter'] ?? 'top-matches',
        ]);
    }
}
