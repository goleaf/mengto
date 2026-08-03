<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationAuditEvent;
use App\Models\User;

final class OrganizationAudit
{
    /** @param array<string, mixed> $metadata */
    public function record(
        Organization $organization,
        ?User $actor,
        string $eventType,
        string $reasonCode,
        string $summaryTranslationKey,
        ?User $subject = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): OrganizationAuditEvent {
        $attributes = [
            'organization_id' => $organization->id,
            'actor_user_id' => $actor?->id,
            'subject_user_id' => $subject?->id,
            'event_type' => $eventType,
            'reason_code' => $reasonCode,
            'summary_translation_key' => $summaryTranslationKey,
            'metadata' => $metadata,
            'created_at' => now(),
        ];

        if ($idempotencyKey === null) {
            return OrganizationAuditEvent::query()->create($attributes);
        }

        return OrganizationAuditEvent::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            $attributes,
        );
    }
}
