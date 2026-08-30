<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceWarningAppealStatus;
use Database\Factories\PlaceWarningAppealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceWarningAppeal extends Model
{
    /** @use HasFactory<PlaceWarningAppealFactory> */
    use HasFactory;

    protected $fillable = [
        'place_warning_id', 'appellant_user_id', 'reviewer_user_id', 'idempotency_key',
        'reason', 'evidence', 'status', 'decision_reason', 'decided_at',
    ];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['status' => PlaceWarningAppealStatus::class, 'decided_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<PlaceWarning, $this> */
    public function warning(): BelongsTo
    {
        return $this->belongsTo(PlaceWarning::class, 'place_warning_id');
    }

    /** @return BelongsTo<User, $this> */
    public function appellant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appellant_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
