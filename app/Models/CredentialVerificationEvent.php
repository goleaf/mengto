<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CredentialVerificationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class CredentialVerificationEvent extends Model
{
    /** @use HasFactory<CredentialVerificationEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'credential_id',
        'actor_user_id',
        'event_type',
        'from_status',
        'to_status',
        'reason_translation_key',
        'internal_reason',
        'idempotency_key',
        'metadata',
    ];

    protected $hidden = [
        'internal_reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Credential verification events are append-only.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Credential verification events are append-only.');
        });
    }

    /** @return BelongsTo<Credential, $this> */
    public function credential(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
