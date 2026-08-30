<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use Database\Factories\UserOnboardingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property OnboardingStep $current_step
 * @property int $id
 * @property Carbon|null $introduction_completed_at
 * @property int $lock_version
 * @property OnboardingPetChoice|null $pet_relationship_choice
 * @property Carbon|null $pet_relationship_completed_at
 * @property Carbon|null $preferences_completed_at
 * @property Carbon|null $privacy_discovery_completed_at
 * @property Carbon $started_at
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property-read User $user
 */
final class UserOnboarding extends Model
{
    /** @use HasFactory<UserOnboardingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_step',
        'pet_relationship_choice',
        'started_at',
        'introduction_completed_at',
        'preferences_completed_at',
        'pet_relationship_completed_at',
        'privacy_discovery_completed_at',
        'completed_at',
        'lock_version',
    ];

    protected function casts(): array
    {
        return [
            'current_step' => OnboardingStep::class,
            'pet_relationship_choice' => OnboardingPetChoice::class,
            'started_at' => 'immutable_datetime',
            'introduction_completed_at' => 'immutable_datetime',
            'preferences_completed_at' => 'immutable_datetime',
            'pet_relationship_completed_at' => 'immutable_datetime',
            'privacy_discovery_completed_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    public function isComplete(): bool
    {
        return $this->current_step === OnboardingStep::Complete
            && $this->completed_at !== null;
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
