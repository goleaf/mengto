<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PetProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $birth_date
 * @property string|null $breed
 * @property Carbon|null $created_at
 * @property Carbon|null $deleted_at
 * @property int $id
 * @property string $name
 * @property array<string, mixed>|null $profile_data
 * @property string $profile_key
 * @property string $slug
 * @property string $species
 * @property string $status
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property int $user_id
 * @property string $visibility
 */
final class PetProfile extends Model
{
    /** @use HasFactory<PetProfileFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'profile_key',
        'slug',
        'name',
        'species',
        'breed',
        'birth_date',
        'visibility',
        'status',
        'profile_data',
    ];

    protected $hidden = ['profile_data'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'immutable_date',
            'profile_data' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(function (Builder $visibility) use ($user): void {
                $visibility->where('visibility', 'public');

                if ($user !== null) {
                    $visibility->orWhere('user_id', $user->id);
                }
            });
    }
}
