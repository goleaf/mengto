<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseRequest;
use App\Services\PreviewService;
use Illuminate\Contracts\View\View;

class NotificationCenterPreviewController extends Controller
{
    public function __invoke(BrowseRequest $request, PreviewService $preview): View
    {
        $data = $preview->notificationCenterData();
        $parameters = $request->validated();
        $activeFilter = $parameters['filter'] ?? 'all-activity';
        $categories = [
            'mentions' => ['Reply'],
            'walks' => ['Meetup'],
            'groups' => ['Group'],
        ];

        if (isset($categories[$activeFilter])) {
            $data['activityGroups'] = array_values(array_filter(array_map(
                static fn (array $group): array => [
                    ...$group,
                    'items' => array_values(array_filter(
                        $group['items'],
                        static fn (array $item): bool => in_array($item['category'], $categories[$activeFilter], true),
                    )),
                ],
                $data['activityGroups'],
            ), static fn (array $group): bool => $group['items'] !== []));
        }

        return view('pet-social.notifications.index', [
            ...$data,
            'activeFilter' => $activeFilter,
        ]);
    }
}
