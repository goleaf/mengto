<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumMentorProfileState;
use Carbon\CarbonImmutable;
use Database\Factories\ForumMentorProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property array<string, mixed> $availability
 * @property int $capacity
 * @property list<string> $communication_preferences
 * @property string $headline
 * @property int $id
 * @property bool $is_public
 * @property list<string> $languages
 * @property string|null $location_scope
 * @property int $lock_version
 * @property CarbonImmutable|null $safety_acknowledged_at
 * @property ForumMentorProfileState $state
 * @property string $summary
 * @property string $timezone
 * @property int $user_id
 * @property-read User $user
 */
final class ForumMentorProfile extends Model
{
    /** @use HasFactory<ForumMentorProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'state',
        'headline',
        'summary',
        'languages',
        'location_scope',
        'timezone',
        'communication_preferences',
        'availability',
        'capacity',
        'is_public',
        'safety_acknowledged_at',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'state' => ForumMentorProfileState::class,
            'languages' => 'array',
            'communication_preferences' => 'array',
            'availability' => 'array',
            'capacity' => 'integer',
            'is_public' => 'boolean',
            'safety_acknowledged_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ForumMentorScope, $this> */
    public function scopes(): HasMany
    {
        return $this->hasMany(ForumMentorScope::class);
    }

    /** @return HasMany<ForumMentorship, $this> */
    public function mentorships(): HasMany
    {
        return $this->hasMany(ForumMentorship::class, 'mentor_user_id', 'user_id');
    }
}
