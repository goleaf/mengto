<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\PreparedTopicData;
use App\Enums\ForumTopicStatus;
use App\Services\ForumActor;
use App\Services\ForumTaxonomy;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PrepareTopicData
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly ForumTaxonomy $taxonomy,
        private readonly StorePublicImage $storePublicImage,
        private readonly FilesystemManager $filesystems,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $existingMedia
     */
    public function handle(array $data, array $existingMedia = []): PreparedTopicData
    {
        $identity = $this->actor->identity();
        $user = $this->actor->requireUser();
        $pet = $this->taxonomy->pets()[$data['pet_key'] ?? ''] ?? null;
        $body = trim((string) $data['body']);
        $category = $this->taxonomy->resolveCategoryKey((string) $data['category']);

        if (filled($data['tried'] ?? null)) {
            $body .= "\n\nWhat I have already tried:\n".trim((string) $data['tried']);
        }

        if (filled($data['veterinary_status'] ?? null) && ($data['is_medical'] ?? false)) {
            $body .= "\n\nVeterinary contact status: ".str_replace('-', ' ', (string) $data['veterinary_status']).'.';
        }

        $status = $data['intent'] === 'draft'
            ? ForumTopicStatus::Draft
            : ForumTopicStatus::Published;

        $storedMedia = $this->storeMedia($data);

        return new PreparedTopicData(attributes: [
            'author_id' => $user->id,
            'author_key' => $identity['key'],
            'author_name' => $identity['name'],
            'author_initials' => $identity['initials'],
            'author_role' => $identity['role'],
            'type' => $data['type'],
            'forum_topic_type_id' => $this->taxonomy->topicTypeId((string) $data['type']),
            'title' => trim((string) $data['title']),
            'body' => $body,
            'category' => $category,
            'forum_category_id' => $this->taxonomy->categoryId($category),
            'subcategory' => $data['subcategory'] ?? null,
            'tags' => $this->tags((string) ($data['tags'] ?? '')),
            'pet_key' => filled($data['pet_key'] ?? null) ? $data['pet_key'] : null,
            'pet_name' => $pet['name'] ?? null,
            'pet_species' => $pet['species'] ?? null,
            'pet_age_label' => $pet['age'] ?? null,
            'location' => $data['location'] ?? null,
            'status' => $status,
            'visibility' => $data['visibility'],
            'desired_answer' => $data['desired_answer'] ?? null,
            'comment_policy' => $data['comment_policy'],
            'language' => $data['language'],
            'media' => [...$existingMedia, ...$storedMedia['media']],
            'is_urgent' => (bool) ($data['is_urgent'] ?? false),
            'is_medical' => (bool) ($data['is_medical'] ?? false),
            'last_activity_at' => now(),
            'last_author_update_at' => now(),
            'state_entered_at' => now(),
            'published_at' => $status === ForumTopicStatus::Published ? now() : null,
        ], newMediaPaths: $storedMedia['paths']);
    }

    public function discardNewMedia(PreparedTopicData $prepared): void
    {
        if ($prepared->newMediaPaths !== []) {
            $this->filesystems->disk('public')->delete($prepared->newMediaPaths);
        }
    }

    /** @return array<int, string> */
    private function tags(string $tags): array
    {
        return Str::of($tags)
            ->explode(',')
            ->map(fn (string $tag): string => Str::lower(trim($tag)))
            ->filter()
            ->unique()
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{media: list<array<string, mixed>>, paths: list<string>}
     */
    private function storeMedia(array $data): array
    {
        $media = [];
        $paths = [];

        try {
            foreach ($data['photos'] ?? [] as $photo) {
                if (! $photo instanceof UploadedFile) {
                    continue;
                }

                $path = $this->storePublicImage->handle($photo, 'forum/images');
                $paths[] = $path;
                $media[] = [
                    'type' => 'image',
                    'path' => $path,
                    'alt' => trim((string) $data['photo_alt']),
                    'sensitive' => (bool) ($data['sensitive_media'] ?? false),
                ];
            }

            if (($data['video'] ?? null) instanceof UploadedFile) {
                $videoPath = $this->storeUpload($data['video'], 'forum/videos', 'video');
                $paths[] = $videoPath;
                $captionPath = null;

                if (($data['video_captions'] ?? null) instanceof UploadedFile) {
                    $captionPath = $this->storeUpload(
                        $data['video_captions'],
                        'forum/captions',
                        'video_captions',
                    );
                    $paths[] = $captionPath;
                }

                $media[] = [
                    'type' => 'video',
                    'path' => $videoPath,
                    'alt' => trim((string) $data['photo_alt']),
                    'transcript' => trim((string) $data['video_transcript']),
                    'caption_path' => $captionPath,
                    'caption_locale' => $captionPath !== null
                        ? (string) $data['video_caption_locale']
                        : null,
                    'sensitive' => (bool) ($data['sensitive_media'] ?? false),
                ];
            }
        } catch (Throwable $exception) {
            if ($paths !== []) {
                $this->filesystems->disk('public')->delete($paths);
            }

            throw $exception;
        }

        return ['media' => $media, 'paths' => $paths];
    }

    private function storeUpload(UploadedFile $upload, string $directory, string $validationKey): string
    {
        $path = $upload->store($directory, 'public');

        if (! is_string($path)) {
            throw ValidationException::withMessages([
                $validationKey => __('forum_accessibility.validation.media_storage_failed'),
            ]);
        }

        return $path;
    }
}
