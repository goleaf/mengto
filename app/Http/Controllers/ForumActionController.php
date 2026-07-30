<?php

namespace App\Http\Controllers;

use App\Actions\PerformForumAction;
use App\Http\Requests\PerformForumActionRequest;
use Illuminate\Http\RedirectResponse;

class ForumActionController extends Controller
{
    public function __invoke(
        PerformForumActionRequest $request,
        PerformForumAction $performForumAction,
    ): RedirectResponse {
        $result = $performForumAction->handle($request->validated());

        if ($result['article'] !== null) {
            return to_route('knowledge.articles.show', $result['article'])
                ->with('feedback', $result['message']);
        }

        if ($result['topic'] !== null) {
            return to_route('forum.topics.show', $result['topic'])
                ->with('feedback', $result['message']);
        }

        return to_route('forum.index')->with('feedback', $result['message']);
    }
}
