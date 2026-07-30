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
            return to_route('pet-social.knowledge.articles.show', $result['article'])
                ->with('pawcircle.feedback', $result['message']);
        }

        if ($result['topic'] !== null) {
            return to_route('pet-social.forum.topics.show', $result['topic'])
                ->with('pawcircle.feedback', $result['message']);
        }

        return to_route('pet-social.forum.index')->with('pawcircle.feedback', $result['message']);
    }
}
