<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceCompatibilityBackfillFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceCompatibilityBackfill extends Model
{
    /** @use HasFactory<PlaceCompatibilityBackfillFactory> */
    use HasFactory;

    protected $fillable = [
        'user_domain_state_id',
        'user_id',
        'contribution_type',
        'legacy_key',
        'payload_checksum',
        'target_type',
        'target_id',
        'status',
        'error_code',
    ];

    /** @return BelongsTo<UserDomainState, $this> */
    public function sourceState(): BelongsTo
    {
        return $this->belongsTo(UserDomainState::class, 'user_domain_state_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
