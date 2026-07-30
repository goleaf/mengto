<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseMessagesRequest;
use App\Services\PawCircleMessagePresenter;
use Illuminate\Contracts\View\View;

final class ConversationDetailPreviewController extends Controller
{
    public function __invoke(
        string $conversation,
        BrowseMessagesRequest $request,
        PawCircleMessagePresenter $presenter,
    ): View {
        return view('pet-social.messages.index', $presenter->page([
            ...$request->validated(),
            'conversation' => $conversation,
            'panel' => 'context',
        ], true));
    }
}
