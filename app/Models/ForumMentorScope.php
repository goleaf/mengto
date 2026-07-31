<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumMentorshipType;
use Database\Factories\ForumMentorScopeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $forum_category_id
 * @property int $forum_mentor_profile_id
 * @property int $id
 * @property bool $is_active
 * @property ForumMentorshipType $mentorship_type
 * @property bool $requires_verified_expertise
 * @property int|null $taxon_id
 * @property-read ForumCategory|null $category
 * @property-read ForumMentorProfile $profile
 * @property-read Taxon|null $taxon
 */
final class ForumMentorScope extends Model
{
    /** @use HasFactory<ForumMentorScopeFactory> */
    use HasFactory;

    protected $fillable = [
        'forum_mentor_profile_id',
        'mentorship_type',
        'forum_category_id',
        'taxon_id',
        'experience_summary',
        'requires_verified_expertise',
        'is_active',
        'scope_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'mentorship_type' => ForumMentorshipType::class,
            'requires_verified_expertise' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<ForumMentorProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(ForumMentorProfile::class, 'forum_mentor_profile_id');
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    /** @return HasMany<ForumMentorship, $this> */
    public function mentorships(): HasMany
    {
        return $this->hasMany(ForumMentorship::class);
    }
}
