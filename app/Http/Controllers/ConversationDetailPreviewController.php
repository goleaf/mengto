<?php

namespace App\Http\Controllers;

use App\Services\PawCirclePreviewService;
use Illuminate\Contracts\View\View;

class ConversationDetailPreviewController extends Controller
{
    public function __invoke(string $conversation, PawCirclePreviewService $preview): View
    {
        $data = $preview->conversationDetailsData($conversation);

        abort_if($data === null, 404);

        return view('pet-social.messages.details', $data);
    }
}
