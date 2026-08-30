<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventVerificationStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventRegistrationPetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property CarbonImmutable|null $checked_in_at
 * @property CarbonImmutable|null $checked_out_at
 * @property string|null $conditions
 * @property ForumEventVerificationStatus $eligibility_status
 * @property int $forum_event_registration_id
 * @property int $id
 * @property int $pet_profile_id
 * @property ForumEventVerificationStatus $verification_source
 */
final class ForumEventRegistrationPet extends Pivot
{
    /** @use HasFactory<ForumEventRegistrationPetFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'forum_event_registration_pets';

    protected $fillable = [
        'forum_event_registration_id',
        'pet_profile_id',
        'eligibility_status',
        'verification_source',
        'conditions',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $hidden = ['conditions'];

    protected $attributes = [
        'eligibility_status' => 'not_assessed',
        'verification_source' => 'unknown',
    ];

    protected function casts(): array
    {
        return [
            'eligibility_status' => ForumEventVerificationStatus::class,
            'verification_source' => ForumEventVerificationStatus::class,
            'conditions' => 'encrypted',
            'checked_in_at' => 'immutable_datetime',
            'checked_out_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumEventRegistration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(ForumEventRegistration::class, 'forum_event_registration_id');
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function petProfile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class)->withTrashed();
    }
}
