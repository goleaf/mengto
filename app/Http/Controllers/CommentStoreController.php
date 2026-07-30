<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateComment;
use App\Http\Requests\StoreCommentRequest;
use App\Models\ForumTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommentStoreController extends Controller
{
    public function __invoke(
        StoreCommentRequest $request,
        ForumTopic $forumTopic,
        CreateComment $createComment,
    ): RedirectResponse {
        Gate::authorize('comment', $forumTopic);
        $createComment->handle($forumTopic, $request->validated());

        return to_route('forum.topics.show', $forumTopic)
            ->with('feedback', __('forum.feedback.comment_added'));
    }
}
