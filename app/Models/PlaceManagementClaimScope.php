<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceManagementScope;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceManagementClaimScopeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable $created_at
 * @property int $id
 * @property int $place_management_claim_id
 * @property PlaceManagementScope $scope
 */
final class PlaceManagementClaimScope extends Model
{
    /** @use HasFactory<PlaceManagementClaimScopeFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['place_management_claim_id', 'scope', 'created_at'];

    protected function casts(): array
    {
        return [
            'scope' => PlaceManagementScope::class,
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<PlaceManagementClaim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(PlaceManagementClaim::class, 'place_management_claim_id');
    }
}
