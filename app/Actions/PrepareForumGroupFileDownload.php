<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumGroup;
use App\Models\ForumGroupFile;
use App\Models\User;
use App\Services\PrivateFileResponse;
use Illuminate\Contracts\Auth\Access\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PrepareForumGroupFileDownload
{
    public function __construct(
        private readonly Gate $gate,
        private readonly PrivateFileResponse $privateFiles,
    ) {}

    public function handle(
        User $actor,
        ForumGroup $group,
        ForumGroupFile $file,
    ): StreamedResponse {
        $this->gate->forUser($actor)->authorize('view', $file);

        if ($file->forum_group_id !== $group->id) {
            abort(404);
        }

        return $this->privateFiles->download(
            disk: $file->disk,
            path: $file->path,
            allowedDirectory: 'forum-groups/'.$group->stable_key,
            downloadName: $file->original_name,
            headers: ['Content-Type' => $file->mime_type],
        );
    }
}
