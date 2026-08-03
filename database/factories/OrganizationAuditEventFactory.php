<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\OrganizationAuditEvent;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<OrganizationAuditEvent> */
final class OrganizationAuditEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'actor_user_id' => null,
            'subject_user_id' => null,
            'event_type' => 'factory-event',
            'reason_code' => 'factory-reason',
            'summary_translation_key' => 'organizations.audit.created',
            'metadata' => null,
            'idempotency_key' => 'factory:organization-audit:'.Str::lower((string) Str::ulid()),
            'created_at' => now(),
        ];
    }
}
