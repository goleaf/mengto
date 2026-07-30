<?php

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
            ? to_route('pet-social.forum.index')->with('pawcircle.feedback', 'Draft saved.')
            : to_route('pet-social.forum.topics.show', $topic)->with('pawcircle.feedback', 'Topic published.');
    }
}
