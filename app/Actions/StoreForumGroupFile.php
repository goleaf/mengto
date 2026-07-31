<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupFileStatus;
use App\Models\ForumGroup;
use App\Models\ForumGroupFile;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class StoreForumGroupFile
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(
        User $actor,
        ForumGroup $group,
        UploadedFile $upload,
        ?string $description,
        string $idempotencyKey,
    ): ForumGroupFile {
        $this->gate->forUser($actor)->authorize('create', [ForumGroupFile::class, $group]);
        Validator::make(
            ['upload' => $upload, 'description' => $description],
            [
                'upload' => [
                    'required',
                    'file',
                    'max:10240',
                    'mimetypes:application/pdf,text/plain,image/jpeg,image/png,image/webp',
                ],
                'description' => ['nullable', 'string', 'max:1000'],
            ],
            attributes: [
                'upload' => __('forum_polls.fields.file'),
                'description' => __('forum_polls.fields.file_description'),
            ],
        )->validate();

        $existing = ForumGroupFile::query()
            ->where('upload_idempotency_key', $idempotencyKey)
            ->first();

        if ($existing !== null) {
            if ($existing->forum_group_id !== $group->id
                || $existing->uploaded_by_user_id !== $actor->id
            ) {
                throw ValidationException::withMessages([
                    'groupFile' => __('forum_polls.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        $stableKey = 'group-file-'.Str::lower((string) Str::ulid());
        $extension = $upload->guessExtension() ?: 'bin';
        $checksum = hash_file('sha256', $upload->getRealPath());
        $path = $upload->storeAs(
            "forum-groups/{$group->stable_key}",
            "{$stableKey}.{$extension}",
            'local',
        );

        if (! is_string($path)) {
            throw ValidationException::withMessages([
                'groupFile' => __('forum_polls.validation.file_storage'),
            ]);
        }

        try {
            return DB::transaction(function () use (
                $actor,
                $description,
                $group,
                $idempotencyKey,
                $path,
                $stableKey,
                $checksum,
                $upload,
            ): ForumGroupFile {
                return ForumGroupFile::query()->create([
                    'forum_group_id' => $group->id,
                    'uploaded_by_user_id' => $actor->id,
                    'stable_key' => $stableKey,
                    'upload_idempotency_key' => $idempotencyKey,
                    'disk' => 'local',
                    'path' => $path,
                    'original_name' => Str::limit(
                        basename($upload->getClientOriginalName()),
                        255,
                        '',
                    ),
                    'mime_type' => $upload->getMimeType() ?: 'application/octet-stream',
                    'byte_size' => $upload->getSize(),
                    'checksum' => $checksum,
                    'description' => filled($description) ? trim((string) $description) : null,
                    'status' => ForumGroupFileStatus::Active,
                ]);
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }
    }
}
