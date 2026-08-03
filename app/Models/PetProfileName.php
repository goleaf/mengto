<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PetProfileNameType;
use App\Enums\PetProfileNameVisibility;
use Database\Factories\PetProfileNameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pet_profile_id
 * @property string $name
 * @property string $normalized_name
 * @property PetProfileNameType $type
 * @property PetProfileNameVisibility $visibility
 * @property string|null $locale
 * @property bool $is_searchable
 * @property int|null $recorded_by_user_id
 * @property Carbon $recorded_at
 */
final class PetProfileName extends Model
{
    /** @use HasFactory<PetProfileNameFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'pet_profile_id',
        'name',
        'normalized_name',
        'type',
        'visibility',
        'locale',
        'is_searchable',
        'recorded_by_user_id',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => PetProfileNameType::class,
            'visibility' => PetProfileNameVisibility::class,
            'is_searchable' => 'boolean',
            'recorded_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<User, $this> */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
