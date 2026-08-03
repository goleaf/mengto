<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\OrganizationAuditEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $actor_user_id
 * @property CarbonImmutable $created_at
 * @property string $event_type
 * @property int $id
 * @property string|null $idempotency_key
 * @property array<string, mixed>|null $metadata
 * @property int $organization_id
 * @property string $reason_code
 * @property int|null $subject_user_id
 * @property string $summary_translation_key
 * @property-read Organization $organization
 */
final class OrganizationAuditEvent extends Model
{
    /** @use HasFactory<OrganizationAuditEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'organization_id',
        'actor_user_id',
        'subject_user_id',
        'event_type',
        'reason_code',
        'summary_translation_key',
        'metadata',
        'idempotency_key',
        'created_at',
    ];

    protected $hidden = ['metadata', 'idempotency_key'];

    protected function casts(): array
    {
        return [
            'metadata' => 'encrypted:array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }
}
