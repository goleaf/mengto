<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PrepareForumJournalMediaResponse;
use App\Models\ForumJournal;
use App\Models\ForumJournalMedia;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ForumJournalMediaController extends Controller
{
    public function __invoke(
        ForumJournal $forumJournal,
        ForumJournalMedia $forumJournalMedia,
        PrepareForumJournalMediaResponse $prepareResponse,
    ): StreamedResponse {
        return $prepareResponse->handle($forumJournal, $forumJournalMedia);
    }
}
