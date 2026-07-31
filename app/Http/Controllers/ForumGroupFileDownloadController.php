<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PrepareForumGroupFileDownload;
use App\Models\ForumGroup;
use App\Models\ForumGroupFile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ForumGroupFileDownloadController extends Controller
{
    public function __invoke(
        ForumGroup $forumGroup,
        ForumGroupFile $file,
        PrepareForumGroupFileDownload $download,
    ): StreamedResponse {
        abort_unless($file->forum_group_id === $forumGroup->id, 404);
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $download->handle($user, $file);
    }
}
