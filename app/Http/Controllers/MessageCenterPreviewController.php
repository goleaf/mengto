<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowsePawCircleRequest;
use App\Services\PawCircleDirectoryFilter;
use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class MessageCenterPreviewController extends Controller
{
    public function __invoke(
        BrowsePawCircleRequest $request,
        PawCirclePreviewService $preview,
        PawCircleDirectoryFilter $filter,
    ): View {
        $parameters = $request->validated();
        $data = $preview->messageCenterData($parameters['conversation'] ?? 'ari');
        $data['conversations'] = $filter->apply(
            $data['conversations'],
            $parameters['q'] ?? null,
            $parameters['filter'] ?? null,
            null,
            [
                'unread' => ['2'],
                'walk-plans' => ['planned'],
            ],
            ['name', 'pet', 'preview', 'unread', 'walk_plan'],
        );

        return view('pet-social.messages.index', [
            ...$data,
            'conversationQuery' => $parameters['q'] ?? '',
            'activeFilter' => $parameters['filter'] ?? 'all',
            'threadFirst' => isset($parameters['conversation']),
        ]);
    }
}
