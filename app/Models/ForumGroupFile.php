<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumGroupFileStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumGroupFileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable|null $archived_at
 * @property int $byte_size
 * @property string $checksum
 * @property string|null $description
 * @property string $disk
 * @property int $forum_group_id
 * @property int $id
 * @property string $mime_type
 * @property string $original_name
 * @property string $path
 * @property string $stable_key
 * @property ForumGroupFileStatus $status
 * @property string $upload_idempotency_key
 * @property int $uploaded_by_user_id
 * @property-read ForumGroup $group
 * @property-read User $uploader
 */
final class ForumGroupFile extends Model
{
    /** @use HasFactory<ForumGroupFileFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_group_id',
        'uploaded_by_user_id',
        'stable_key',
        'upload_idempotency_key',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'byte_size',
        'checksum',
        'description',
        'status',
        'archived_at',
    ];

    protected $hidden = [
        'upload_idempotency_key',
        'disk',
        'path',
        'original_name',
        'checksum',
    ];

    protected $attributes = [
        'disk' => 'local',
        'status' => 'active',
    ];

    protected function casts(): array
    {
        return [
            'byte_size' => 'integer',
            'status' => ForumGroupFileStatus::class,
            'archived_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
