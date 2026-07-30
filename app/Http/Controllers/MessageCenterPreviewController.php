<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseMessagesRequest;
use App\Services\PawCircleMessagePresenter;
use Illuminate\Contracts\View\View;

final class MessageCenterPreviewController extends Controller
{
    public function __invoke(
        BrowseMessagesRequest $request,
        PawCircleMessagePresenter $presenter,
    ): View {
        return view('pet-social.messages.index', $presenter->page($request->validated()));
    }
}
