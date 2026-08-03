<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ForumTopicType;
use App\Services\ForumTopicTypeSchemaRegistry;

final readonly class ForumTopicTypeObserver
{
    public function __construct(
        private ForumTopicTypeSchemaRegistry $registry,
    ) {}

    public function saved(ForumTopicType $topicType): void
    {
        $this->registry->invalidate();
    }

    public function deleted(ForumTopicType $topicType): void
    {
        $this->registry->invalidate();
    }
}
