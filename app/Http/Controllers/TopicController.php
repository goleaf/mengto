<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ForumTopic;
use App\Services\ForumPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class TopicController extends Controller
{
    public function __invoke(
        ForumTopic $forumTopic,
        ForumPresenter $presenter,
    ): View|RedirectResponse {
        if ($forumTopic->status->redirectsToAnotherTopic()) {
            $target = $forumTopic->redirectionTarget()
                ->select([
                    'id',
                    'slug',
                    'type',
                    'forum_group_id',
                    'author_key',
                    'visibility',
                    'status',
                    'legal_hold_at',
                ])
                ->firstOrFail();
            Gate::authorize('view', $target);

            return redirect()->route(
                'forum.topics.show',
                $target,
                301,
            );
        }

        Gate::authorize('view', $forumTopic);

        return view('forum.show', $presenter->topic($forumTopic));
    }
}
