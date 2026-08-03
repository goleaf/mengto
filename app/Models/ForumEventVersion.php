<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\ForumEventVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable $created_at
 * @property int|null $created_by_user_id
 * @property int $forum_event_id
 * @property int $id
 * @property string $kind
 * @property list<string>|null $material_fields
 * @property CarbonImmutable|null $published_at
 * @property string $reason_code
 * @property array<string, mixed> $snapshot
 * @property string $snapshot_checksum
 * @property int $version_number
 */
final class ForumEventVersion extends Model
{
    /** @use HasFactory<ForumEventVersionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'forum_event_id',
        'version_number',
        'created_by_user_id',
        'kind',
        'reason_code',
        'snapshot',
        'snapshot_checksum',
        'material_fields',
        'published_at',
        'created_at',
    ];

    protected $hidden = ['snapshot'];

    protected $attributes = ['kind' => 'draft'];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'snapshot' => 'encrypted:array',
            'material_fields' => 'array',
            'published_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumEvent, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(ForumEvent::class, 'forum_event_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return HasMany<ForumEventRegistration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(ForumEventRegistration::class);
    }
}
