<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlaceWarningConfirmationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property CarbonImmutable $confirmed_at */
final class PlaceWarningConfirmation extends Model
{
    /** @use HasFactory<PlaceWarningConfirmationFactory> */
    use HasFactory;

    protected $fillable = ['place_warning_id', 'user_id', 'idempotency_key', 'confirmed_at'];

    protected $hidden = ['idempotency_key'];

    protected function casts(): array
    {
        return ['confirmed_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<PlaceWarning, $this> */
    public function warning(): BelongsTo
    {
        return $this->belongsTo(PlaceWarning::class, 'place_warning_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
