<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OnboardingPetChoice;
use App\Enums\OnboardingStep;
use Database\Factories\UserOnboardingFactory;
use DateTimeImmutable;
use DateTimeInterface;
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

    protected $guarded = ['*'];

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
        return $this->persistedStep() === OnboardingStep::Complete
            && $this->persistedPetChoice() instanceof OnboardingPetChoice
            && $this->hasPersistedTimestamp('started_at')
            && $this->hasPersistedTimestamp('introduction_completed_at')
            && $this->hasPersistedTimestamp('preferences_completed_at')
            && $this->hasPersistedTimestamp('pet_relationship_completed_at')
            && $this->hasPersistedTimestamp('privacy_discovery_completed_at')
            && $this->hasPersistedTimestamp('completed_at')
            && ($this->persistedLockVersion() ?? 0) >= OnboardingStep::Complete->position();
    }

    public function persistedStep(): ?OnboardingStep
    {
        $value = $this->getRawOriginal('current_step');

        return is_string($value) ? OnboardingStep::tryFrom($value) : null;
    }

    public function persistedPetChoice(): ?OnboardingPetChoice
    {
        $value = $this->getRawOriginal('pet_relationship_choice');

        return is_string($value) ? OnboardingPetChoice::tryFrom($value) : null;
    }

    public function persistedLockVersion(): ?int
    {
        $value = $this->getRawOriginal('lock_version');

        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }

    public function hasPersistedTimestamp(string $attribute): bool
    {
        $value = $this->getRawOriginal($attribute);

        if ($value instanceof DateTimeInterface) {
            return true;
        }

        if (! is_string($value) || $value === '') {
            return false;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);
        $errors = DateTimeImmutable::getLastErrors();

        return $parsed instanceof DateTimeImmutable
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $parsed->format('Y-m-d H:i:s') === $value;
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
