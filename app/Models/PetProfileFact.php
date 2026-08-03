<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PetEvidenceStatus;
use App\Enums\PetProfileVisibility;
use Database\Factories\PetProfileFactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $fact_key
 * @property bool $is_current
 * @property Carbon|null $retired_at
 * @property array<string, mixed> $value
 * @property PetProfileVisibility $visibility
 */
final class PetProfileFact extends Model
{
    /** @use HasFactory<PetProfileFactFactory> */
    use HasFactory;

    protected $fillable = [
        'pet_profile_id',
        'fact_key',
        'value',
        'normalized_value_hash',
        'precision',
        'source_type',
        'source_reference',
        'author_user_id',
        'verification_status',
        'visibility',
        'is_current',
        'current_key',
        'replaces_fact_id',
        'recorded_at',
        'retired_at',
        'metadata',
    ];

    protected $hidden = ['value', 'normalized_value_hash', 'source_reference', 'metadata'];

    protected function casts(): array
    {
        return [
            'value' => 'encrypted:array',
            'source_reference' => 'encrypted',
            'verification_status' => PetEvidenceStatus::class,
            'visibility' => PetProfileVisibility::class,
            'is_current' => 'boolean',
            'recorded_at' => 'immutable_datetime',
            'retired_at' => 'immutable_datetime',
            'metadata' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /** @return BelongsTo<PetProfileFact, $this> */
    public function replacedFact(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_fact_id');
    }
}
