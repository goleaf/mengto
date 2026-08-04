<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PetIdentifyingMarkType;
use App\Enums\PetIdentifyingMarkVisibility;
use Database\Factories\PetProfileIdentifyingMarkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $description
 * @property int $id
 * @property string $mark_key
 * @property int $pet_profile_id
 * @property int $position
 * @property Carbon|null $retired_at
 * @property PetIdentifyingMarkType $type
 * @property PetIdentifyingMarkVisibility $visibility
 */
final class PetProfileIdentifyingMark extends Model
{
    /** @use HasFactory<PetProfileIdentifyingMarkFactory> */
    use HasFactory;

    protected $fillable = [
        'mark_key',
        'pet_profile_id',
        'type',
        'description',
        'visibility',
        'position',
        'created_by_user_id',
        'updated_by_user_id',
        'retired_at',
    ];

    protected $hidden = ['description'];

    protected $attributes = [
        'visibility' => 'verification',
        'position' => 0,
    ];

    protected function casts(): array
    {
        return [
            'type' => PetIdentifyingMarkType::class,
            'description' => 'encrypted',
            'visibility' => PetIdentifyingMarkVisibility::class,
            'position' => 'integer',
            'retired_at' => 'immutable_datetime',
        ];
    }

    /** @param Builder<self> $query @return Builder<self> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('retired_at');
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
