<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BrowseContentFeedRequest;
use App\Models\User;
use App\Services\ContentChronologicalFeed;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\View\View;

final class ContentFeedController extends Controller
{
    public function __invoke(
        BrowseContentFeedRequest $request,
        ContentChronologicalFeed $feed,
        ProfilePresenter $profiles,
    ): View {
        $validated = $request->validated();
        $viewer = $request->user();

        return view('content.index', [
            'owner' => $profiles->owner(),
            'page_title' => __('content.feed.page_title'),
            'empty_title' => __('content.feed.empty_title'),
            'empty_description' => __('content.feed.empty_description'),
            'feed' => $feed->page(
                $viewer instanceof User ? $viewer : null,
                (int) ($validated['per_page'] ?? 15),
            ),
        ]);
    }
}
