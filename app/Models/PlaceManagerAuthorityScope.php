<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceManagementScope;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceManagerAuthorityScopeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property int $place_manager_authority_id
 * @property PlaceManagementScope $scope
 */
final class PlaceManagerAuthorityScope extends Model
{
    /** @use HasFactory<PlaceManagerAuthorityScopeFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['place_manager_authority_id', 'scope', 'created_at'];

    protected function casts(): array
    {
        return [
            'scope' => PlaceManagementScope::class,
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<PlaceManagerAuthority, $this> */
    public function authority(): BelongsTo
    {
        return $this->belongsTo(PlaceManagerAuthority::class, 'place_manager_authority_id');
    }
}
