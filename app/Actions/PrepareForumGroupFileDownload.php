<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumGroupFile;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PrepareForumGroupFileDownload
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(User $actor, ForumGroupFile $file): StreamedResponse
    {
        $this->gate->forUser($actor)->authorize('view', $file);

        if (! Storage::disk($file->disk)->exists($file->path)) {
            abort(404);
        }

        return Storage::disk($file->disk)->download(
            $file->path,
            $file->original_name,
            ['Content-Type' => $file->mime_type],
        );
    }
}
