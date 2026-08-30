<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceWarningDisputeStatus;
use Database\Factories\PlaceWarningDisputeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceWarningDispute extends Model
{
    /** @use HasFactory<PlaceWarningDisputeFactory> */
    use HasFactory;

    protected $fillable = [
        'place_warning_id', 'disputant_user_id', 'reviewer_user_id', 'idempotency_key',
        'reason', 'evidence', 'status', 'decision_reason', 'decided_at',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['status' => PlaceWarningDisputeStatus::class, 'decided_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<PlaceWarning, $this> */
    public function warning(): BelongsTo
    {
        return $this->belongsTo(PlaceWarning::class, 'place_warning_id');
    }

    /** @return BelongsTo<User, $this> */
    public function disputant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disputant_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
