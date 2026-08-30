<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateTopic;
use App\Http\Requests\StoreTopicRequest;
use App\Models\ForumTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TopicStoreController extends Controller
{
    public function __invoke(StoreTopicRequest $request, CreateTopic $createTopic): RedirectResponse
    {
        Gate::authorize('create', ForumTopic::class);
        $topic = $createTopic->handle($request->validated());

        return $topic->status->value === 'draft'
            ? to_route('forum.index')->with('feedback', __('messages.draft_saved'))
            : to_route('forum.topics.show', $topic)->with('feedback', __('messages.topic_published'));
    }
}
