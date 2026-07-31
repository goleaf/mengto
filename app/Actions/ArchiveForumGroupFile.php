<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupFileStatus;
use App\Models\ForumGroupFile;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;

final class ArchiveForumGroupFile
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(User $actor, ForumGroupFile $file): ForumGroupFile
    {
        $this->gate->forUser($actor)->authorize('delete', $file);

        return DB::transaction(function () use ($actor, $file): ForumGroupFile {
            $lockedFile = ForumGroupFile::query()->lockForUpdate()->findOrFail($file->id);
            $this->gate->forUser($actor)->authorize('delete', $lockedFile);

            if ($lockedFile->status === ForumGroupFileStatus::Active) {
                $lockedFile->forceFill([
                    'status' => ForumGroupFileStatus::Archived,
                    'archived_at' => now(),
                ])->save();
            }

            return $lockedFile->refresh();
        }, 3);
    }
}
