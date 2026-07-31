<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateForumGroupAnnouncementData;
use App\Models\ForumGroup;
use App\Models\ForumGroupAnnouncement;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PublishForumGroupAnnouncement
{
    public function __construct(private readonly Gate $gate) {}

    public function handle(
        User $actor,
        ForumGroup $group,
        CreateForumGroupAnnouncementData $data,
    ): ForumGroupAnnouncement {
        $this->gate->forUser($actor)->authorize(
            'create',
            [ForumGroupAnnouncement::class, $group],
        );
        $this->validate($data);

        return DB::transaction(function () use ($actor, $group, $data): ForumGroupAnnouncement {
            $existing = ForumGroupAnnouncement::query()
                ->where('publication_idempotency_key', $data->idempotencyKey)
                ->first();

            if ($existing !== null) {
                if ($existing->forum_group_id !== $group->id
                    || $existing->author_user_id !== $actor->id
                ) {
                    throw ValidationException::withMessages([
                        'announcement' => __('forum_polls.validation.idempotency_conflict'),
                    ]);
                }

                return $existing;
            }

            return ForumGroupAnnouncement::query()->create([
                'forum_group_id' => $group->id,
                'author_user_id' => $actor->id,
                'stable_key' => 'group-announcement-'.Str::lower((string) Str::ulid()),
                'publication_idempotency_key' => $data->idempotencyKey,
                'title' => trim($data->title),
                'body' => trim($data->body),
                'published_at' => $data->publishedAt,
                'expires_at' => $data->expiresAt,
            ]);
        }, 3);
    }

    private function validate(CreateForumGroupAnnouncementData $data): void
    {
        $errors = [];

        if (mb_strlen(trim($data->title)) < 4 || mb_strlen(trim($data->title)) > 180) {
            $errors['announcementTitle'] = __('forum_polls.validation.announcement_title');
        }

        if (mb_strlen(trim($data->body)) < 10 || mb_strlen(trim($data->body)) > 10000) {
            $errors['announcementBody'] = __('forum_polls.validation.announcement_body');
        }

        if ($data->expiresAt !== null && ! $data->expiresAt->isAfter($data->publishedAt)) {
            $errors['announcementExpiresAt'] = __('forum_polls.validation.announcement_dates');
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
