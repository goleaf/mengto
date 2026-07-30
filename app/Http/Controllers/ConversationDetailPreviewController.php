<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseMessagesRequest;
use App\Services\MessagePresenter;
use Illuminate\Contracts\View\View;

final class ConversationDetailPreviewController extends Controller
{
    public function __invoke(
        string $conversation,
        BrowseMessagesRequest $request,
        MessagePresenter $presenter,
    ): View {
        return view('messages.index', $presenter->page([
            ...$request->validated(),
            'conversation' => $conversation,
            'panel' => 'context',
        ], true));
    }
}
