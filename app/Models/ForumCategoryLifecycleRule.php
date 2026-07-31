<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumCategoryLifecycleRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $archive_review_after_days
 * @property bool $allow_author_archive
 * @property bool $allow_author_remove
 * @property bool $allow_author_reopen
 * @property bool $allow_bumping
 * @property bool $auto_archive_enabled
 * @property int $bump_cooldown_hours
 * @property int $forum_category_id
 * @property int $id
 * @property bool $is_system_managed
 * @property array<array-key, mixed>|null $metadata
 * @property int $necropost_after_days
 * @property int|null $retention_review_after_days
 * @property int $rules_version
 * @property int $stale_after_days
 * @property int|null $updated_by_user_id
 */
final class ForumCategoryLifecycleRule extends Model
{
    /** @use HasFactory<ForumCategoryLifecycleRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_category_id',
        'stale_after_days',
        'necropost_after_days',
        'archive_review_after_days',
        'retention_review_after_days',
        'bump_cooldown_hours',
        'allow_author_reopen',
        'allow_author_archive',
        'allow_author_remove',
        'allow_bumping',
        'auto_archive_enabled',
        'rules_version',
        'is_system_managed',
        'updated_by_user_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'stale_after_days' => 'integer',
            'necropost_after_days' => 'integer',
            'archive_review_after_days' => 'integer',
            'retention_review_after_days' => 'integer',
            'bump_cooldown_hours' => 'integer',
            'allow_author_reopen' => 'boolean',
            'allow_author_archive' => 'boolean',
            'allow_author_remove' => 'boolean',
            'allow_bumping' => 'boolean',
            'auto_archive_enabled' => 'boolean',
            'rules_version' => 'integer',
            'is_system_managed' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
