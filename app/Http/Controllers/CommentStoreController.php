<?php

namespace App\Http\Controllers;

use App\Actions\CreateComment;
use App\Http\Requests\StoreCommentRequest;
use App\Models\ForumTopic;
use Illuminate\Http\RedirectResponse;

class CommentStoreController extends Controller
{
    public function __invoke(
        StoreCommentRequest $request,
        ForumTopic $forumTopic,
        CreateComment $createComment,
    ): RedirectResponse {
        $createComment->handle($forumTopic, $request->validated());

        return to_route('forum.topics.show', $forumTopic)
            ->with('feedback', 'Comment added.');
    }
}
