<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PrepareForumJournalExport;
use App\Models\ForumJournal;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ForumJournalExportController extends Controller
{
    public function __invoke(
        Request $request,
        ForumJournal $forumJournal,
        PrepareForumJournalExport $prepareExport,
    ): StreamedResponse {
        $actor = $request->user();

        if (! $actor instanceof User) {
            abort(401);
        }

        return $prepareExport->handle($actor, $forumJournal);
    }
}
