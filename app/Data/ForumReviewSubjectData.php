<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ForumReviewSubjectData
{
    public function __construct(
        public string $type,
        public int $id,
        public ?int $authorUserId,
        public string $title,
        public string $excerpt,
        public bool $isMedical,
        public bool $containsPrivateEvidence,
    ) {}

    /** @return array<string, int|string|bool|null> */
    public function publicContext(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'author_user_id' => $this->authorUserId,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'is_medical' => $this->isMedical,
            'contains_private_evidence' => false,
        ];
    }
}
