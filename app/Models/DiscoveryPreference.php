<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DiscoveryCategory;
use App\Enums\DiscoveryPreferenceScope;
use Database\Factories\DiscoveryPreferenceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property DiscoveryCategory $category
 * @property int $id
 * @property string|null $reason_code
 * @property DiscoveryPreferenceScope $scope
 * @property string $target_key
 * @property int $user_id
 * @property-read User $user
 */
final class DiscoveryPreference extends Model
{
    /** @use HasFactory<DiscoveryPreferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scope',
        'category',
        'target_key',
        'reason_code',
    ];

    protected function casts(): array
    {
        return [
            'scope' => DiscoveryPreferenceScope::class,
            'category' => DiscoveryCategory::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param Builder<DiscoveryPreference> $query */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
}
