<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumGroupFileStatus;
use App\Models\ForumGroup;
use App\Models\ForumGroupFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumGroupFile>
 */
final class ForumGroupFileFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());
        $contents = "Private group fixture {$key}\n";

        return [
            'forum_group_id' => ForumGroup::factory(),
            'uploaded_by_user_id' => null,
            'stable_key' => "group-file-{$key}",
            'upload_idempotency_key' => "factory:group-file:{$key}",
            'disk' => 'local',
            'path' => "forum-groups/factories/{$key}.txt",
            'original_name' => 'group-reference.txt',
            'mime_type' => 'text/plain',
            'byte_size' => strlen($contents),
            'checksum' => hash('sha256', $contents),
            'description' => fake()->sentence(),
            'status' => ForumGroupFileStatus::Active,
        ];
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(static function (ForumGroupFile $file): void {
                if ($file->forum_group_id !== null) {
                    $file->uploaded_by_user_id = ForumGroup::query()
                        ->whereKey($file->forum_group_id)
                        ->value('owner_user_id');
                }
            })
            ->afterCreating(static function (ForumGroupFile $file): void {
                if (! Storage::disk($file->disk)->exists($file->path)) {
                    Storage::disk($file->disk)->put(
                        $file->path,
                        'Private group fixture '
                        .str($file->stable_key)->after('group-file-')->toString()
                        ."\n",
                    );
                }
            });
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumGroupFileStatus::Archived,
            'archived_at' => now(),
        ]);
    }
}
